<?php
/**
 * WP-CLI import commands for Athens Groundhopper content.
 *
 * Only loaded under WP-CLI (see the guard below and the require in
 * functions.php) — this code never runs on a normal web request.
 *
 * Usage:
 *   wp ag-import grounds       data/grounds.csv
 *   wp ag-import clubs         data/clubs.csv
 *   wp ag-import fixtures      data/fixtures.csv
 *   wp ag-import campaigns     data/campaigns.csv
 *   wp ag-import competitions  data/competitions.csv
 *
 * Run them in that order — clubs reference grounds by title, fixtures
 * reference both clubs and grounds, campaigns reference grounds,
 * competitions reference clubs.
 *
 * Common flags on every subcommand:
 *   --status=<status>   Post status for created/updated posts. Default: publish.
 *   --dry-run            Preview only — resolves lookups and validates rows,
 *                         but creates/changes nothing. Always try this first
 *                         on a file from a new source.
 *
 * Matching is by TITLE within the post type: re-running the same CSV (e.g.
 * after a union sends a correction) updates the existing post instead of
 * creating a duplicate.
 *
 * @package Athens_Groundhopper
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Athens_Groundhopper_Import_Command {

	/** @var bool */
	private $dry_run = false;

	/** @var string */
	private $status = 'publish';

	/**
	 * Import Grounds from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--status=<status>]
	 * : Post status to use. Default: publish.
	 *
	 * [--dry-run]
	 * : Preview only, writes nothing.
	 *
	 * ## CSV COLUMNS
	 *
	 * title (required), content, address, lat, lng, surface, capacity,
	 * length_m, width_m, has_changing_rooms, has_lighting, has_stands,
	 * union, osm_id, aed_status, aed_checked_on, photo_url
	 *
	 * has_changing_rooms / has_lighting / has_stands: yes or no.
	 *
	 * aed_status must be one of: confirmed, unconfirmed, none-confirmed.
	 * Anything else (including blank) is imported as "unconfirmed" — never
	 * guessed as "non-compliant".
	 *
	 * ## EXAMPLES
	 *
	 *     wp ag-import grounds data/grounds.csv --dry-run
	 *     wp ag-import grounds data/grounds.csv
	 *
	 * @when after_wp_load
	 */
	public function grounds( $args, $assoc_args ) {
		$this->prepare_flags( $assoc_args );
		$rows = $this->read_csv( $args[0] );

		$counts = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
		);

		foreach ( $rows as $i => $row ) {
			$line = $i + 2; // +1 for header row, +1 for 1-based counting

			$title = trim( $row['title'] ?? '' );
			if ( '' === $title ) {
				WP_CLI::warning( "Row {$line}: missing title — skipped." );
				++$counts['skipped'];
				continue;
			}

			$union = sanitize_text_field( $row['union'] ?? '' );

			$aed_status = $this->validate_choice(
				$row['aed_status'] ?? '',
				array( 'confirmed', 'unconfirmed', 'none-confirmed' ),
				'unconfirmed',
				"Row {$line} ({$title}): unrecognised aed_status \"" . ( $row['aed_status'] ?? '' ) . '", using "unconfirmed".'
			);

			// Disambiguate by union where we have one: two different unions
			// can genuinely each have a ground called e.g. "ΜΟΣΧΑΤΟΥ" — without
			// this, the second one imported would overwrite the first.
			list( $post_id, $created ) = $this->upsert_post(
				'ground',
				$title,
				$row['content'] ?? '',
				'',
				$union ? array( 'ag_union' => $union ) : array()
			);
			if ( null === $post_id ) {
				++$counts['skipped'];
				continue;
			}

			$this->set_meta( $post_id, array(
				'ag_address'             => sanitize_text_field( $row['address'] ?? '' ),
				'ag_lat'                 => is_numeric( $row['lat'] ?? null ) ? (float) $row['lat'] : '',
				'ag_lng'                 => is_numeric( $row['lng'] ?? null ) ? (float) $row['lng'] : '',
				'ag_surface'             => sanitize_text_field( $row['surface'] ?? '' ),
				'ag_capacity'            => is_numeric( $row['capacity'] ?? null ) ? (int) $row['capacity'] : '',
				'ag_length_m'            => is_numeric( $row['length_m'] ?? null ) ? (float) $row['length_m'] : '',
				'ag_width_m'             => is_numeric( $row['width_m'] ?? null ) ? (float) $row['width_m'] : '',
				'ag_has_changing_rooms'  => $this->yes_no( $row['has_changing_rooms'] ?? '' ),
				'ag_has_lighting'        => $this->yes_no( $row['has_lighting'] ?? '' ),
				'ag_has_stands'          => $this->yes_no( $row['has_stands'] ?? '' ),
				'ag_union'               => $union,
				'ag_osm_id'              => sanitize_text_field( $row['osm_id'] ?? '' ),
				'ag_aed_status'          => $aed_status,
				'ag_aed_checked_on'      => sanitize_text_field( $row['aed_checked_on'] ?? '' ),
			) );

			$this->maybe_set_thumbnail( $post_id, $row['photo_url'] ?? '', $title );

			$counts[ $created ? 'created' : 'updated' ]++;
			WP_CLI::log( ( $this->dry_run ? '[dry-run] ' : '' ) . ( $created ? 'Created' : 'Updated' ) . " ground: {$title}" );
		}

		$this->summary( 'Grounds', $counts );
	}

	/**
	 * Import Clubs from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--status=<status>]
	 * : Post status to use. Default: publish.
	 *
	 * [--dry-run]
	 * : Preview only, writes nothing.
	 *
	 * ## CSV COLUMNS
	 *
	 * title (required), content, home_ground (ground title, must already
	 * exist), address, email, gga_code, union, competition (taxonomy term
	 * name or slug; created if it doesn't exist), photo_url
	 *
	 * union is optional: if left blank, it's taken from the matched
	 * home_ground's own union instead of needing to be scraped separately.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ag-import clubs data/clubs.csv --dry-run
	 *
	 * @when after_wp_load
	 */
	public function clubs( $args, $assoc_args ) {
		$this->prepare_flags( $assoc_args );
		$rows = $this->read_csv( $args[0] );

		$counts = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
		);

		foreach ( $rows as $i => $row ) {
			$line  = $i + 2;
			$title = trim( $row['title'] ?? '' );
			if ( '' === $title ) {
				WP_CLI::warning( "Row {$line}: missing title — skipped." );
				++$counts['skipped'];
				continue;
			}

			$ground_id = 0;
			$derived_union = '';
			$ground_title = trim( $row['home_ground'] ?? '' );
			if ( '' !== $ground_title ) {
				$ground_id = $this->find_post_id_by_title( 'ground', $ground_title );
				if ( $ground_id ) {
					$derived_union = get_post_meta( $ground_id, 'ag_union', true );
				} else {
					WP_CLI::warning( "Row {$line} ({$title}): home_ground \"{$ground_title}\" not found — import grounds.csv first. Left blank." );
				}
			}
			// Explicit union column wins if present; otherwise take whatever
			// union the matched ground belongs to, so a clubs.csv without a
			// union column at all still gets it right.
			$union = trim( $row['union'] ?? '' );
			if ( '' === $union ) {
				$union = $derived_union;
			}

			list( $post_id, $created ) = $this->upsert_post( 'club', $title, $row['content'] ?? '' );
			if ( null === $post_id ) {
				++$counts['skipped'];
				continue;
			}

			$this->set_meta( $post_id, array(
				'ag_home_ground_id' => $ground_id ?: '',
				'ag_union'          => sanitize_text_field( $union ),
				'ag_address'        => sanitize_text_field( $row['address'] ?? '' ),
				'ag_email'          => sanitize_email( $row['email'] ?? '' ),
				'ag_gga_code'       => sanitize_text_field( $row['gga_code'] ?? '' ),
			) );

			$this->maybe_set_terms( $post_id, 'competition', $row['competition'] ?? '' );
			$this->maybe_set_thumbnail( $post_id, $row['photo_url'] ?? '', $title );

			$counts[ $created ? 'created' : 'updated' ]++;
			WP_CLI::log( ( $this->dry_run ? '[dry-run] ' : '' ) . ( $created ? 'Created' : 'Updated' ) . " club: {$title}" );
		}

		$this->summary( 'Clubs', $counts );
	}

	/**
	 * Import Fixtures from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--status=<status>]
	 * : Post status to use. Default: publish.
	 *
	 * [--dry-run]
	 * : Preview only, writes nothing.
	 *
	 * ## CSV COLUMNS
	 *
	 * home_club, away_club (club titles, must already exist), kickoff
	 * (required — ISO 8601, e.g. 2026-10-18T17:00:00 — becomes the post's
	 * publish date per the "post_date IS kickoff" convention, so Post Date
	 * displays it correctly with no extra code), ground (ground title),
	 * home_score, away_score, status (scheduled|live|finished|postponed),
	 * source, door_price, vibe_note, transit_note, competition (taxonomy
	 * term name or slug)
	 *
	 * title is NOT a column — it's always generated as "Home vs Away" from
	 * home_club/away_club, matching the theme's display convention.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ag-import fixtures data/fixtures.csv --dry-run
	 *
	 * @when after_wp_load
	 */
	public function fixtures( $args, $assoc_args ) {
		$this->prepare_flags( $assoc_args );
		$rows = $this->read_csv( $args[0] );

		$counts = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
		);

		foreach ( $rows as $i => $row ) {
			$line = $i + 2;

			$home_title = trim( $row['home_club'] ?? '' );
			$away_title = trim( $row['away_club'] ?? '' );
			$kickoff    = trim( $row['kickoff'] ?? '' );

			if ( '' === $home_title || '' === $away_title ) {
				WP_CLI::warning( "Row {$line}: missing home_club or away_club — skipped." );
				++$counts['skipped'];
				continue;
			}
			$kickoff_ts = $kickoff ? strtotime( $kickoff ) : false;
			if ( ! $kickoff_ts ) {
				WP_CLI::warning( "Row {$line} ({$home_title} vs {$away_title}): missing or unparseable kickoff — skipped." );
				++$counts['skipped'];
				continue;
			}

			$home_id = $this->find_post_id_by_title( 'club', $home_title );
			$away_id = $this->find_post_id_by_title( 'club', $away_title );
			if ( ! $home_id ) {
				WP_CLI::warning( "Row {$line}: home_club \"{$home_title}\" not found — import clubs.csv first. Left blank." );
			}
			if ( ! $away_id ) {
				WP_CLI::warning( "Row {$line}: away_club \"{$away_title}\" not found — import clubs.csv first. Left blank." );
			}

			$ground_id = 0;
			$ground_title = trim( $row['ground'] ?? '' );
			if ( '' !== $ground_title ) {
				$ground_id = $this->find_post_id_by_title( 'ground', $ground_title );
				if ( ! $ground_id ) {
					WP_CLI::warning( "Row {$line}: ground \"{$ground_title}\" not found — import grounds.csv first. Left blank." );
				}
			}

			$title  = "{$home_title} vs {$away_title}";
			$status = $this->validate_choice(
				$row['status'] ?? '',
				array( 'scheduled', 'live', 'finished', 'postponed' ),
				'scheduled',
				"Row {$line} ({$title}): unrecognised status, using \"scheduled\"."
			);

			list( $post_id, $created ) = $this->upsert_post( 'fixture', $title, '', gmdate( 'Y-m-d H:i:s', $kickoff_ts ) );
			if ( null === $post_id ) {
				++$counts['skipped'];
				continue;
			}

			$this->set_meta( $post_id, array(
				'ag_kickoff'      => gmdate( 'c', $kickoff_ts ),
				'ag_home_club_id' => $home_id ?: '',
				'ag_away_club_id' => $away_id ?: '',
				'ag_ground_id'    => $ground_id ?: '',
				'ag_home_score'   => is_numeric( $row['home_score'] ?? null ) ? (int) $row['home_score'] : '',
				'ag_away_score'   => is_numeric( $row['away_score'] ?? null ) ? (int) $row['away_score'] : '',
				'ag_status'       => $status,
				'ag_source'       => sanitize_text_field( $row['source'] ?? '' ),
				'ag_door_price'   => sanitize_text_field( $row['door_price'] ?? '' ),
				'ag_vibe_note'    => sanitize_textarea_field( $row['vibe_note'] ?? '' ),
				'ag_transit_note' => sanitize_textarea_field( $row['transit_note'] ?? '' ),
			) );

			$this->maybe_set_terms( $post_id, 'competition', $row['competition'] ?? '' );

			$counts[ $created ? 'created' : 'updated' ]++;
			WP_CLI::log( ( $this->dry_run ? '[dry-run] ' : '' ) . ( $created ? 'Created' : 'Updated' ) . " fixture: {$title}" );
		}

		$this->summary( 'Fixtures', $counts );
	}

	/**
	 * Import AED Campaigns from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--status=<status>]
	 * : Post status to use. Default: publish.
	 *
	 * [--dry-run]
	 * : Preview only, writes nothing.
	 *
	 * ## CSV COLUMNS
	 *
	 * title (required), content, ground (ground title; blank = general
	 * fund), goal_amount, raised_amount, currency (default EUR), status
	 * (active|funded|installed), installed_on, photo_url
	 *
	 * ## EXAMPLES
	 *
	 *     wp ag-import campaigns data/campaigns.csv --dry-run
	 *
	 * @when after_wp_load
	 */
	public function campaigns( $args, $assoc_args ) {
		$this->prepare_flags( $assoc_args );
		$rows = $this->read_csv( $args[0] );

		$counts = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
		);

		foreach ( $rows as $i => $row ) {
			$line  = $i + 2;
			$title = trim( $row['title'] ?? '' );
			if ( '' === $title ) {
				WP_CLI::warning( "Row {$line}: missing title — skipped." );
				++$counts['skipped'];
				continue;
			}

			$ground_id = 0;
			$ground_title = trim( $row['ground'] ?? '' );
			if ( '' !== $ground_title ) {
				$ground_id = $this->find_post_id_by_title( 'ground', $ground_title );
				if ( ! $ground_id ) {
					WP_CLI::warning( "Row {$line} ({$title}): ground \"{$ground_title}\" not found — import grounds.csv first. Left blank (general fund)." );
				}
			}

			$status = $this->validate_choice(
				$row['status'] ?? '',
				array( 'active', 'funded', 'installed' ),
				'active',
				"Row {$line} ({$title}): unrecognised status, using \"active\"."
			);

			list( $post_id, $created ) = $this->upsert_post( 'campaign', $title, $row['content'] ?? '' );
			if ( null === $post_id ) {
				++$counts['skipped'];
				continue;
			}

			$this->set_meta( $post_id, array(
				'ag_ground_id'     => $ground_id ?: '',
				'ag_goal_amount'   => is_numeric( $row['goal_amount'] ?? null ) ? (float) $row['goal_amount'] : '',
				'ag_raised_amount' => is_numeric( $row['raised_amount'] ?? null ) ? (float) $row['raised_amount'] : 0,
				'ag_currency'      => sanitize_text_field( $row['currency'] ?? 'EUR' ) ?: 'EUR',
				'ag_status'        => $status,
				'ag_installed_on'  => sanitize_text_field( $row['installed_on'] ?? '' ),
			) );

			$this->maybe_set_thumbnail( $post_id, $row['photo_url'] ?? '', $title );

			$counts[ $created ? 'created' : 'updated' ]++;
			WP_CLI::log( ( $this->dry_run ? '[dry-run] ' : '' ) . ( $created ? 'Created' : 'Updated' ) . " campaign: {$title}" );
		}

		$this->summary( 'Campaigns', $counts );
	}

	/**
	 * Tag Clubs with their competition/division, building a two-level
	 * taxonomy as it goes: union as the parent term, division as the
	 * child (e.g. "EPSA Athens" > "Α Κατηγορία 1ος Όμιλος"). Re-running
	 * replaces a club's competition rather than adding to it, so a
	 * promotion/relegation update is just a re-import.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--dry-run]
	 * : Preview only, writes and creates nothing.
	 *
	 * ## CSV COLUMNS
	 *
	 * union (required — epsa, epspeir, epsana, or epsda; the parent term's
	 * display name is derived from this, see ag_union_label() in
	 * functions.php), division (required — the child term, exactly as the
	 * union names it, e.g. "Α Κατηγορία 1ος Όμιλος"), club (required —
	 * must match a club already imported; matched with the same
	 * accent/case-insensitive fallback as every other lookup here)
	 *
	 * ## EXAMPLES
	 *
	 *     wp ag-import competitions data/competitions.csv --dry-run
	 *
	 * @when after_wp_load
	 */
	public function competitions( $args, $assoc_args ) {
		$this->prepare_flags( $assoc_args );
		$rows = $this->read_csv( $args[0] );

		$counts = array(
			'tagged'        => 0,
			'skipped'       => 0,
			'terms_created' => 0,
		);
		$parent_cache = array(); // union code => term_id, so we only look it up once per run

		foreach ( $rows as $i => $row ) {
			$line       = $i + 2;
			$union_code = strtolower( trim( $row['union'] ?? '' ) );
			$division   = trim( $row['division'] ?? '' );
			$club_title = trim( $row['club'] ?? '' );

			if ( '' === $union_code || '' === $division || '' === $club_title ) {
				WP_CLI::warning( "Row {$line}: missing union, division, or club — skipped." );
				++$counts['skipped'];
				continue;
			}

			$club_id = $this->find_post_id_by_title( 'club', $club_title );
			if ( ! $club_id ) {
				WP_CLI::warning( "Row {$line}: club \"{$club_title}\" not found — import clubs.csv first. Skipped." );
				++$counts['skipped'];
				continue;
			}

			$union_label = function_exists( 'ag_union_label' ) ? ag_union_label( $union_code ) : strtoupper( $union_code );

			if ( $this->dry_run ) {
				WP_CLI::log( "[dry-run] Would tag \"{$club_title}\": {$union_label} > {$division}" );
				++$counts['tagged'];
				continue;
			}

			if ( ! isset( $parent_cache[ $union_code ] ) ) {
				$parent_cache[ $union_code ] = $this->ensure_term( $union_label, 0, $counts );
			}
			$child_id = $this->ensure_term( $division, $parent_cache[ $union_code ], $counts );

			wp_set_object_terms( $club_id, array( $child_id ), 'competition', false );

			++$counts['tagged'];
			WP_CLI::log( "Tagged \"{$club_title}\": {$union_label} > {$division}" );
		}

		WP_CLI::success(
			sprintf(
				'%d tagged, %d skipped, %d new term(s) created%s.',
				$counts['tagged'],
				$counts['skipped'],
				$counts['terms_created'],
				$this->dry_run ? ' (dry run)' : ''
			)
		);
	}

	/**
	 * Find-or-create a `competition` term under a given parent. Matches by
	 * (name, parent) together via term_exists()'s parent argument, since
	 * two different unions can genuinely both have a division literally
	 * named "Α Κατηγορία" — matching by name alone would conflate them.
	 */
	private function ensure_term( $name, $parent_id, &$counts ) {
		$existing = term_exists( $name, 'competition', $parent_id );
		if ( $existing ) {
			return (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
		}
		$result = wp_insert_term( $name, 'competition', array( 'parent' => $parent_id ) );
		if ( is_wp_error( $result ) ) {
			// Likely a same-name-and-parent race with another row in this
			// same run — re-check rather than fail the whole import.
			$again = term_exists( $name, 'competition', $parent_id );
			if ( $again ) {
				return (int) ( is_array( $again ) ? $again['term_id'] : $again );
			}
			WP_CLI::error( "Couldn't create term \"{$name}\": " . $result->get_error_message() );
		}
		++$counts['terms_created'];
		return (int) $result['term_id'];
	}

	// ---------------------------------------------------------------
	// Shared helpers
	// ---------------------------------------------------------------

	private function prepare_flags( $assoc_args ) {
		$this->dry_run = isset( $assoc_args['dry-run'] );
		$this->status  = $assoc_args['status'] ?? 'publish';
	}

	private function read_csv( $path ) {
		if ( ! is_readable( $path ) ) {
			WP_CLI::error( "Can't read file: {$path}" );
		}

		// Peek at the raw header line to pick a delimiter. Most files here
		// are comma-separated, but a file that's been opened and re-saved
		// in Excel under a Greek/European locale often comes back
		// semicolon-separated instead — whichever character splits the
		// header into more pieces is almost certainly the real delimiter.
		$peek = fopen( $path, 'r' );
		$raw_header_line = (string) fgets( $peek );
		fclose( $peek );
		$raw_header_line = preg_replace( '/^\xEF\xBB\xBF/', '', $raw_header_line );
		$delimiter = substr_count( $raw_header_line, ';' ) > substr_count( $raw_header_line, ',' ) ? ';' : ',';

		$handle = fopen( $path, 'r' );
		$header = fgetcsv( $handle, 0, $delimiter );
		if ( ! $header ) {
			WP_CLI::error( "File has no header row: {$path}" );
		}
		// Strip a UTF-8 BOM if present on the first column name, and trim
		// every column name — both are common CSV-export gremlins.
		$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );
		$header    = array_map( 'trim', $header );

		WP_CLI::log(
			'Columns detected (' . ( ',' === $delimiter ? 'comma' : 'semicolon' ) . '-separated): '
			. implode( ', ', $header )
		);

		$rows = array();
		while ( false !== ( $data = fgetcsv( $handle, 0, $delimiter ) ) ) {
			if ( 1 === count( $data ) && null === $data[0] ) {
				continue; // blank line
			}
			$data     = array_slice( array_pad( $data, count( $header ), '' ), 0, count( $header ) );
			$rows[] = array_combine( $header, $data );
		}
		fclose( $handle );

		WP_CLI::log( count( $rows ) . " row(s) read from {$path}." . ( $this->dry_run ? ' (dry run — nothing will be written)' : '' ) );
		return $rows;
	}

	/**
	 * Create or update a post by matching on (post_type, title).
	 *
	 * @return array{0: int|null, 1: bool} [post_id or null, was_created]
	 */
	private function upsert_post( $post_type, $title, $content = '', $post_date = '', $meta_match = array() ) {
		$existing_id = $this->find_post_id_by_title( $post_type, $title, $meta_match );

		$postarr = array(
			'post_type'    => $post_type,
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => $this->status,
		);
		if ( $post_date ) {
			$postarr['post_date']     = $post_date;
			$postarr['post_date_gmt'] = get_gmt_from_date( $post_date );
		}

		if ( $this->dry_run ) {
			return array( $existing_id ?: -1, ! $existing_id );
		}

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$result        = wp_update_post( $postarr, true );
		} else {
			$result = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "Couldn't save \"{$title}\": " . $result->get_error_message() );
			return array( null, false );
		}

		return array( $result, ! $existing_id );
	}

	/**
	 * Strip Greek tonos/diaeresis accents, uppercase, and collapse
	 * whitespace — the same normalization used (by hand, in chat) to match
	 * ground and club names across different union exports at a ~100% hit
	 * rate. Built in here now so every lookup benefits automatically.
	 */
	private function normalize_title( $str ) {
		static $map = array(
			'Ά' => 'Α', 'Έ' => 'Ε', 'Ή' => 'Η', 'Ί' => 'Ι', 'Ό' => 'Ο', 'Ύ' => 'Υ', 'Ώ' => 'Ω',
			'ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ό' => 'ο', 'ύ' => 'υ', 'ώ' => 'ω',
			'ϊ' => 'ι', 'ϋ' => 'υ', 'ΐ' => 'ι', 'ΰ' => 'υ', 'Ϊ' => 'Ι', 'Ϋ' => 'Υ',
		);
		$str = strtr( trim( (string) $str ), $map );
		$str = mb_strtoupper( $str, 'UTF-8' );
		return trim( preg_replace( '/\s+/u', ' ', $str ) );
	}

	/**
	 * $meta_match lets a caller disambiguate two posts that share a title —
	 * e.g. two different grounds both named "ΜΟΣΧΑΤΟΥ", one in EPSA's area
	 * and one in EPSPEIR's. Without it, re-importing the second would
	 * silently overwrite the first instead of creating a second post.
	 *
	 * Falls back to a normalized scan of every post of that type when an
	 * exact match fails — handles accent/case/whitespace differences
	 * between two scrapes of the same club or ground. Cheap in practice:
	 * only runs on a miss, and these collections are a few hundred posts.
	 */
	private function find_post_id_by_title( $post_type, $title, $meta_match = array() ) {
		$args = array(
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		if ( $meta_match ) {
			$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery
			foreach ( $meta_match as $key => $value ) {
				$args['meta_query'][] = array(
					'key'   => $key,
					'value' => $value,
				);
			}
		}
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			return $query->posts[0]->ID;
		}

		$target = $this->normalize_title( $title );
		$all_ids = get_posts( array(
			'post_type'              => $post_type,
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );
		foreach ( $all_ids as $id ) {
			if ( $this->normalize_title( get_the_title( $id ) ) !== $target ) {
				continue;
			}
			if ( $meta_match ) {
				foreach ( $meta_match as $key => $value ) {
					if ( get_post_meta( $id, $key, true ) !== $value ) {
						continue 2;
					}
				}
			}
			return $id;
		}
		return 0;
	}

	private function set_meta( $post_id, $meta ) {
		if ( $this->dry_run || $post_id < 1 ) {
			return;
		}
		foreach ( $meta as $key => $value ) {
			if ( '' === $value ) {
				continue; // don't clobber an existing value with blank on re-import
			}
			update_post_meta( $post_id, $key, $value );
		}
	}

	private function maybe_set_terms( $post_id, $taxonomy, $raw ) {
		$raw = trim( $raw );
		if ( '' === $raw || $this->dry_run || $post_id < 1 ) {
			return;
		}
		$names = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		wp_set_object_terms( $post_id, $names, $taxonomy, false );
	}

	private function maybe_set_thumbnail( $post_id, $url, $title ) {
		$url = trim( $url );
		if ( '' === $url || $this->dry_run || $post_id < 1 ) {
			return;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			return; // don't re-download on every re-import
		}
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );
		if ( is_wp_error( $attachment_id ) ) {
			WP_CLI::warning( "\"{$title}\": couldn't fetch photo_url ({$attachment_id->get_error_message()})." );
			return;
		}
		set_post_thumbnail( $post_id, $attachment_id );
	}

	private function yes_no( $value ) {
		$value = mb_strtolower( trim( $value ) );
		if ( in_array( $value, array( 'yes', 'y', 'true', '1', 'ναι' ), true ) ) {
			return 'yes';
		}
		if ( in_array( $value, array( 'no', 'n', 'false', '0', 'όχι' ), true ) ) {
			return 'no';
		}
		return ''; // unrecognised/blank — set_meta() will skip it rather than guess
	}

	private function validate_choice( $value, $allowed, $default, $warning ) {
		$value = trim( $value );
		if ( in_array( $value, $allowed, true ) ) {
			return $value;
		}
		if ( '' !== $value ) {
			WP_CLI::warning( $warning );
		}
		return $default;
	}

	private function summary( $label, $counts ) {
		WP_CLI::success(
			sprintf(
				'%s — %d created, %d updated, %d skipped%s.',
				$label,
				$counts['created'],
				$counts['updated'],
				$counts['skipped'],
				$this->dry_run ? ' (dry run)' : ''
			)
		);
	}
}

WP_CLI::add_command( 'ag-import', 'Athens_Groundhopper_Import_Command' );
