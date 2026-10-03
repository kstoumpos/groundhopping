<?php
/**
 * Athens Groundhopper theme functions.
 *
 * Registers the custom post types and meta fields that mirror the data
 * architecture from our sourcing research: Grounds, Clubs, Fixtures and
 * AED Campaigns. Fixtures/grounds can be entered by hand for now; the meta
 * fields are REST-exposed (show_in_rest => true) so a future sync script
 * (API-Football, or a union data-sharing export) can populate them the
 * same way an editor would in wp-admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme support.
 */
function ag_theme_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'title-tag' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'ag_theme_setup' );

/**
 * Editor colour/font palette — the classic-theme equivalent of what
 * theme.json's settings.color.palette / typography.fontSizes did for the
 * old block-theme templates. This still matters: Ground and Campaign posts
 * support the block editor for their body content, so editors writing
 * that content should get on-brand swatches, same as before.
 */
function ag_editor_palette_setup() {
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => 'Concrete Dark',  'slug' => 'concrete-dark',   'color' => '#0a0b0d' ),
		array( 'name' => 'Card Dark',      'slug' => 'card-dark',       'color' => '#121316' ),
		array( 'name' => 'Zinc 800',       'slug' => 'zinc-800',        'color' => '#27272a' ),
		array( 'name' => 'Zinc 400',       'slug' => 'zinc-400',        'color' => '#a1a1aa' ),
		array( 'name' => 'Paper Light',    'slug' => 'paper-light',     'color' => '#eaeae2' ),
		array( 'name' => 'AMF Red',        'slug' => 'amf-red',         'color' => '#d90429' ),
		array( 'name' => 'Hazard Yellow',  'slug' => 'hazard-yellow',   'color' => '#ffd60a' ),
		array( 'name' => 'Spray Neon',     'slug' => 'spray-neon',      'color' => '#00f5d4' ),
		array( 'name' => 'WFL Purple',     'slug' => 'wfl-purple',      'color' => '#a855f7' ),
		array( 'name' => 'White',          'slug' => 'white',           'color' => '#ffffff' ),
		array( 'name' => 'Black',          'slug' => 'black',           'color' => '#000000' ),
	) );

	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => 'Body',  'slug' => 'body',  'size' => 15 ),
		array( 'name' => 'H3',    'slug' => 'h3',     'size' => 20 ),
		array( 'name' => 'H2',    'slug' => 'h2',     'size' => 28 ),
		array( 'name' => 'H1',    'slug' => 'h1',     'size' => 40 ),
		array( 'name' => 'Hero',  'slug' => 'hero',   'size' => 56 ),
	) );
}
add_action( 'after_setup_theme', 'ag_editor_palette_setup' );

/**
 * A classic theme needs an explicit nav-menu location if it wants one
 * managed from Appearance → Menus, rather than the hand-typed links
 * currently in header.php. Registered but not yet assigned to a menu —
 * header.php falls back to its hardcoded links until you build one.
 */
function ag_register_nav_menus() {
	register_nav_menus( array(
		'primary' => 'Primary navigation',
	) );
}
add_action( 'after_setup_theme', 'ag_register_nav_menus' );

/**
 * Extra body classes from the original mockup (antialiased, selection:*).
 * header.php calls body_class() the normal classic-theme way; this filter
 * just keeps those utility classes out of a hand-typed class="" attribute.
 */
function ag_body_classes( $classes ) {
	$classes[] = 'antialiased';
	$classes[] = 'selection:bg-amf-red';
	$classes[] = 'selection:text-white';
	return $classes;
}
add_filter( 'body_class', 'ag_body_classes' );

/**
 * Fonts.
 *
 * NOTE: loading fonts straight from fonts.googleapis.com sends EU visitors'
 * IP addresses to Google at request time, which a German court (LG Munich,
 * Jan 2022) held breaches GDPR without consent. Before this theme goes live,
 * download Chakra Petch/IBM Plex Mono/Permanent Marker/Oswald/Inter's woff2 files, put them under assets/fonts/,
 * and swap this for @font-face rules + wp_enqueue_style on a local file.
 * Left as a Google Fonts link here only so the theme is visually complete
 * in local development.
 */
function ag_enqueue_fonts() {
	wp_enqueue_style(
		'ag-google-fonts',
		'https://fonts.googleapis.com/css2'
			. '?family=Chakra+Petch:ital,wght@0,600;0,700;1,700'
			. '&family=IBM+Plex+Mono:ital,wght@0,400;0,600;1,400'
			. '&family=Permanent+Marker'
			. '&family=Oswald:wght@400;500;600;700'
			. '&family=Inter:wght@400;500;600;700'
			. '&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'ag_enqueue_fonts' );

/**
 * Tailwind output. Compiled by `npm run build` from src/input.css into
 * assets/css/tailwind.css — see package.json. WordPress hosting has no
 * Node build step, so this compiled file is committed to the repo like any
 * other asset; running `npm run build` again after editing any .php
 * template or template-parts/ file is a required part of the deploy, not
 * optional tooling.
 */
function ag_enqueue_tailwind() {
	$file = get_theme_file_path( 'assets/css/tailwind.css' );
	wp_enqueue_style(
		'ag-tailwind',
		get_theme_file_uri( 'assets/css/tailwind.css' ),
		array(),
		file_exists( $file ) ? filemtime( $file ) : '0.1.0'
	);
}
add_action( 'wp_enqueue_scripts', 'ag_enqueue_tailwind' );
add_action( 'enqueue_block_editor_assets', 'ag_enqueue_tailwind' );

/**
 * Custom post type: Ground
 * A physical venue. One ground can host many clubs and many fixtures.
 */
function ag_register_ground_cpt() {
	register_post_type( 'ground', array(
		'labels' => array(
			'name'          => 'Grounds',
			'singular_name' => 'Ground',
			'add_new_item'  => 'Add New Ground',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-location-alt',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'grounds' ),
		'template'     => array(
			array( 'core/paragraph', array( 'placeholder' => 'A short description of the ground — who plays here, what it feels like on matchday.' ) ),
		),
	) );

	$ground_meta_fields = array(
		'ag_address'           => 'string',
		'ag_lat'               => 'number',
		'ag_lng'               => 'number',
		'ag_surface'           => 'string',   // grass, artificial_turf, etc. — mirrors the OSM `surface` tag
		'ag_capacity'          => 'integer',
		'ag_length_m'          => 'number',   // pitch length in metres, as published by the union
		'ag_width_m'           => 'number',   // pitch width in metres
		'ag_has_changing_rooms' => 'string', // 'yes' | 'no' — literal strings, not boolean: WP stores a false meta value as '', indistinguishable from unset
		'ag_has_lighting'       => 'string',
		'ag_has_stands'         => 'string',
		'ag_union'             => 'string',   // epsa / epspeir / epsana / epsda / espg / super-league — which body this ground's fixtures come from
		'ag_osm_id'            => 'string',   // OpenStreetMap node/way id, for re-syncing geometry
		'ag_aed_status'        => 'string',   // confirmed | unconfirmed | none-confirmed — never assert "non-compliant"
		'ag_aed_checked_on'    => 'string',   // ISO date of the last verification, shown next to any AED claim
	);
	foreach ( $ground_meta_fields as $key => $type ) {
		register_post_meta( 'ground', $key, array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => $type,
		) );
	}
}
add_action( 'init', 'ag_register_ground_cpt' );

/**
 * Custom post type: Club
 * A team. Has a home ground and belongs to a competition tier.
 */
function ag_register_club_cpt() {
	register_post_type( 'club', array(
		'labels' => array(
			'name'          => 'Clubs',
			'singular_name' => 'Club',
			'add_new_item'  => 'Add New Club',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-groups',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'clubs' ),
	) );

	register_post_meta( 'club', 'ag_home_ground_id', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'integer', // post ID of a `ground`
	) );
	register_post_meta( 'club', 'ag_union', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string',
	) );
	register_post_meta( 'club', 'ag_address', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string', // club office / contact address — distinct from the ground's own address
	) );
	register_post_meta( 'club', 'ag_email', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string',
	) );
	register_post_meta( 'club', 'ag_gga_code', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string', // the club's registration code with the Γενική Γραμματεία Αθλητισμού (General Secretariat of Sports), where a union publishes it instead of contact info
	) );
}
add_action( 'init', 'ag_register_club_cpt' );

/**
 * Custom post type: Fixture
 * One match. Links two clubs and a ground.
 */
function ag_register_fixture_cpt() {
	register_post_type( 'fixture', array(
		'labels' => array(
			'name'          => 'Fixtures',
			'singular_name' => 'Fixture',
			'add_new_item'  => 'Add New Fixture',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-calendar-alt',
		'supports'     => array( 'title', 'custom-fields' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'fixtures' ),
	) );

	$fixture_meta_fields = array(
		'ag_kickoff'      => 'string',  // ISO 8601 datetime
		'ag_home_club_id' => 'integer',
		'ag_away_club_id' => 'integer',
		'ag_ground_id'    => 'integer',
		'ag_home_score'   => 'integer',
		'ag_away_score'   => 'integer',
		'ag_status'       => 'string',  // scheduled | live | finished | postponed
		'ag_source'       => 'string',  // which feed/union this came from, for auditing
		'ag_door_price'   => 'string',  // free-text, e.g. "3€ – 5€" or "Free for U16" — gate prices vary too much to force a number
		'ag_vibe_note'    => 'string',  // one or two sentences: what to expect on the terrace
		'ag_transit_note' => 'string',  // how to get there by metro/bus/tram
	);
	foreach ( $fixture_meta_fields as $key => $type ) {
		register_post_meta( 'fixture', $key, array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => $type,
		) );
	}
}
add_action( 'init', 'ag_register_fixture_cpt' );

/**
 * Custom post type: Campaign
 * An AED fundraising campaign, usually tied to one ground (or none, for
 * the general fund).
 */
function ag_register_campaign_cpt() {
	register_post_type( 'campaign', array(
		'labels' => array(
			'name'          => 'AED Campaigns',
			'singular_name' => 'Campaign',
			'add_new_item'  => 'Add New Campaign',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-heart',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'support' ),
		'template'     => array(
			array( 'core/paragraph', array( 'placeholder' => 'Why this ground needs an AED — two or three sentences, ideally in the club\u2019s own words.' ) ),
		),
	) );

	$campaign_meta_fields = array(
		'ag_ground_id'      => 'integer', // empty/0 = general fund
		'ag_goal_amount'    => 'number',
		'ag_raised_amount'  => 'number',
		'ag_currency'       => 'string',  // EUR
		'ag_status'         => 'string',  // active | funded | installed
		'ag_installed_on'   => 'string',  // ISO date, shown in the "funded so far" list once complete
	);
	foreach ( $campaign_meta_fields as $key => $type ) {
		register_post_meta( 'campaign', $key, array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => $type,
		) );
	}
}
add_action( 'init', 'ag_register_campaign_cpt' );

/**
 * Taxonomy: competition tier.
 * Super League 1, Super League 2, Greek Cup, the four Attica amateur
 * unions' divisions, and the women's top flight — attached to `club`
 * and `fixture` so both can be filtered/queried by tier.
 */
function ag_register_competition_taxonomy() {
	register_taxonomy( 'competition', array( 'club', 'fixture' ), array(
		'labels' => array(
			'name'          => 'Competitions',
			'singular_name' => 'Competition',
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'competition' ),
	) );
}
add_action( 'init', 'ag_register_competition_taxonomy' );

/**
 * WP-CLI content importer (wp ag-import grounds/clubs/fixtures/campaigns).
 * Guarded inside the required file too, but checking here as well means
 * the file is never even opened on a normal web request.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_theme_file_path( 'inc/class-ag-cli-import.php' );
}

/**
 * Fixture convention: a fixture's post_date IS its kickoff time (set it
 * when creating the post), and its post_title is "Home Club vs Away Club".
 * That lets the_title() / the_date() display a fixture correctly with no
 * extra fields. ag_kickoff meta stays in sync for API/export use, but
 * templates should read post_date, not the meta field.
 *
 * Query-arg helpers (classic-theme replacement for the old
 * query_loop_block_query_vars filter): call these into `new WP_Query()`
 * directly in a template, e.g.
 *   new WP_Query( ag_upcoming_fixtures_args( array( 'posts_per_page' => 3 ) ) )
 */
function ag_upcoming_fixtures_args( $extra = array() ) {
	$defaults = array(
		'post_type'      => 'fixture',
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'date_query'     => array(
			array(
				'after'     => 'today',
				'inclusive' => true,
			),
		),
	);
	return array_merge( $defaults, $extra );
}

/**
 * A ground's own AED campaign(s) — scoped by ag_ground_id meta.
 */
function ag_ground_campaign_args( $ground_id, $extra = array() ) {
	$defaults = array(
		'post_type'      => 'campaign',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'   => 'ag_ground_id',
				'value' => (int) $ground_id,
				'type'  => 'NUMERIC',
			),
		),
	);
	return array_merge( $defaults, $extra );
}

/**
 * Helper: format a EUR amount consistently across templates.
 */
function ag_format_eur( $amount ) {
	return '€' . number_format_i18n( (float) $amount, 0 );
}

/**
 * Helper: human-readable union name from its short code (as stored in
 * ag_union meta, and used as the taxonomy's top-level "union" term name).
 * One place to keep this mapping so the importer and templates agree.
 */
function ag_union_label( $code ) {
	$labels = array(
		'epsa'    => 'EPSA Athens',
		'epspeir' => 'EPS Piraeus',
		'epsana'  => 'EPSANA (East Attica)',
		'epsda'   => 'EPSDA (West Attica)',
		'espg'    => "ESPG (Women's)",
	);
	return $labels[ $code ] ?? strtoupper( $code );
}

/**
 * Helper: safe AED status label. Never renders an unverified ground as
 * "non-compliant" — see the sourcing research's legal caveat.
 */
function ag_aed_status_label( $status ) {
	switch ( $status ) {
		case 'confirmed':
			return 'AED confirmed on site';
		case 'none-confirmed':
			return 'No AED confirmed on site';
		default:
			return 'AED status unverified';
	}
}

/**
 * Helper: colour + label for a fixture's competition tag pill, keyed by
 * the `competition` taxonomy term SLUG. Add a case here for every
 * competition tier you create in wp-admin; unknown or unset slugs fall back
 * to a neutral zinc pill rather than guessing a colour.
 *
 * Women's football (any slug containing "women" or "wfl") always gets the
 * wfl-purple treatment regardless of tier, so a new women's competition
 * doesn't need its own case added here.
 */
function ag_competition_pill_classes( $term_slug ) {
	if ( false !== strpos( $term_slug, 'women' ) || false !== strpos( $term_slug, 'wfl' ) ) {
		return 'bg-wfl-purple text-white';
	}

	switch ( $term_slug ) {
		case 'epsa':
		case 'epspeir':
		case 'epsana':
		case 'epsda':
			return 'bg-spray-neon text-black'; // amateur / non-league
		case 'gamma-ethniki':
		case 'super-league-2':
			return 'bg-amf-red text-white'; // semi-pro / historic tiers
		default:
			return 'bg-zinc-800 text-zinc-300';
	}
}
