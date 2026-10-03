<?php
/**
 * Front page.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark text-paper-light font-mono">

	<!-- ============ HERO ============ -->
	<div class="max-w-6xl mx-auto px-4 py-12 sm:py-16 border-b border-zinc-800">
		<div class="grid lg:grid-cols-12 gap-8 items-start">

			<div class="lg:col-span-8">
				<span class="font-tag text-spray-neon text-xl inline-block -rotate-1 mb-3">#RealAthensFootball</span>
				<h1 class="font-display text-4xl sm:text-6xl font-bold uppercase leading-tight tracking-tight mb-5">
					THE REAL ATHENS PITCH. <br>
					<span class="bg-paper-light text-black px-2 inline-block">BEYOND THE VIP BOXES.</span><br>
					EXPERIENCE THE CONCRETE.
				</h1>
				<p class="text-zinc-400 text-sm sm:text-base border-l-2 border-amf-red pl-4 max-w-2xl leading-relaxed mb-4">
					The curated groundhopper manual for Athens and Attica. From historic clubs in Super League 2 to the concrete terraces of EPSA amateur leagues and the Women's Football League (WFL).
				</p>
				<p class="text-xs text-zinc-500 italic">
					* Ένας ανεξάρτητος, δίγλωσσος οδηγός για όσους επισκέπτονται τα γήπεδα της πόλης με σεβασμό στην εξέδρα, στη γειτονιά και στο ερασιτεχνικό ποδόσφαιρο.
				</p>
			</div>

			<!-- Match finder: presentational only for now — no query params or JS wired up yet, same as the donation amount buttons on single-campaign.php. Worth building for real once there's enough Fixture data to make filtering useful. -->
			<div class="lg:col-span-4 bg-card-dark border-2 border-paper-light p-5 shadow-[6px_6px_0px_#d90429]">
				<h3 class="font-display text-lg font-bold uppercase mb-1">// Hopping Match Finder</h3>
				<p class="text-xs text-zinc-400 mb-3">Find an accessible game by Metro &amp; Public Transit:</p>

				<div class="flex gap-1.5 mb-3">
					<button type="button" class="bg-paper-light text-black text-[11px] font-bold px-2.5 py-1 uppercase">All</button>
					<button type="button" class="bg-black border border-zinc-700 text-zinc-300 text-[11px] font-bold px-2.5 py-1 uppercase hover:border-amf-red">Men's</button>
					<button type="button" class="bg-black border border-wfl-purple text-wfl-purple text-[11px] font-bold px-2.5 py-1 uppercase hover:bg-purple-950/40">Women's (WFL)</button>
				</div>

				<label class="text-[11px] text-zinc-400 uppercase font-semibold">Matchday</label>
				<select class="w-full bg-black text-paper-light border border-zinc-700 p-2 text-xs mb-3 focus:outline-none focus:border-amf-red">
					<option>This Weekend (Saturday &amp; Sunday)</option>
					<option>Saturday Afternoon (EPSA Local)</option>
					<option>Sunday Midday (WFL Women's Football)</option>
					<option>Sunday Afternoon (National Leagues)</option>
				</select>

				<label class="text-[11px] text-zinc-400 uppercase font-semibold">Atmosphere &amp; Tier</label>
				<select class="w-full bg-black text-paper-light border border-zinc-700 p-2 text-xs mb-4 focus:outline-none focus:border-amf-red">
					<option>All Divisions (Amateur to Pro)</option>
					<option>EPSA Athens (Raw Neighborhood Grounds)</option>
					<option>EPS Piraeus (Harbor &amp; Western Clubs)</option>
					<option>Women's Football League (WFL)</option>
					<option>Gamma Ethniki (Tier 3 Historic Teams)</option>
					<option>Super League 2 (Crowded Stadiums)</option>
				</select>

				<a href="#radar" class="block text-center w-full bg-amf-red text-white font-display font-bold text-sm uppercase py-3 border-2 border-black shadow-[3px_3px_0px_#fff] hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_#fff] transition">
					Find Fixtures &amp; Guides &rarr;
				</a>
			</div>

		</div>
	</div>

	<!-- ============ TICKETS & LAW ============ -->
	<div id="ticket-rules" class="max-w-6xl mx-auto px-4 py-12 border-b border-zinc-800">
		<div class="border-2 border-spray-neon bg-black p-6 sm:p-8">
			<div class="flex items-center gap-3 mb-4">
				<span class="bg-spray-neon text-black font-display font-bold text-xs px-2 py-0.5 uppercase">Survival Manual for Foreigners</span>
				<span class="text-xs text-zinc-400">// Greek ticketing bureaucracy, safety &amp; etiquette</span>
			</div>

			<div class="grid md:grid-cols-3 gap-6 text-xs leading-relaxed text-zinc-300">
				<div class="border-l-2 border-zinc-700 pl-3">
					<h4 class="font-display text-sm font-bold text-white uppercase mb-1.5">1. Gov.gr Wallet vs. Non-League Cash</h4>
					<p>Super League 1 requires tickets validated via the Gov.gr Wallet app (foreigners scan passports via OCR). <strong>For EPSA amateur, Gamma Ethniki, and WFL women's matches, no app or ID is needed</strong>: pay a few euros cash at the gate for an old-school paper ticket.</p>
				</div>
				<div class="border-l-2 border-zinc-700 pl-3">
					<h4 class="font-display text-sm font-bold text-white uppercase mb-1.5">2. Away Fan Bans &amp; Neutral Travel</h4>
					<p>Organized away fans are banned across most Greek fixtures. As an independent groundhopper, wear strictly neutral streetwear on the metro and around grounds. Keep opposing colors and team scarves zipped inside your bag.</p>
				</div>
				<div class="border-l-2 border-zinc-700 pl-3">
					<h4 class="font-display text-sm font-bold text-white uppercase mb-1.5">3. Curva Respect &amp; Club Canteen</h4>
					<p><strong>Never point cameras or phones at active ultra curves (Petalo) or fans' faces.</strong> Support the host club by buying coffee, water or beer at the local stadium canteen (kylikeio) — it directly funds grassroots gear.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- ============ WEEKEND RADAR (dynamic) ============ -->
	<div id="radar" class="max-w-6xl mx-auto px-4 py-14">

		<div class="flex justify-between items-end border-b-2 border-paper-light pb-2 mb-8">
			<div>
				<h2 class="font-display text-xl sm:text-2xl font-bold uppercase">// Weekend Hopping Radar</h2>
				<p class="text-xs text-zinc-400 mt-1">Curated matchdays with public transit and gate info</p>
			</div>
			<span class="text-xs text-spray-neon uppercase hidden sm:inline">Door Cash (No App Needed)</span>
		</div>

		<?php
		$ag_fixtures = new WP_Query( ag_upcoming_fixtures_args( array( 'posts_per_page' => 3 ) ) );
		if ( $ag_fixtures->have_posts() ) :
			?>
			<div class="grid md:grid-cols-3 gap-6">
				<?php
				while ( $ag_fixtures->have_posts() ) :
					$ag_fixtures->the_post();
					?>
					<div class="bg-card-dark border-2 border-zinc-800 p-5 flex flex-col justify-between">
						<?php get_template_part( 'template-parts/fixture-card', null, array( 'post_id' => get_the_ID() ) ); ?>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php
		else :
			?>
			<p class="text-body text-zinc-400">No fixtures entered yet — add some under Fixtures in wp-admin, with a competition term, door price, vibe note and transit note for the full card.</p>
			<?php
		endif;
		?>

	</div>

	<!-- ============ AED CAMPAIGN (dynamic) ============ -->
	<div id="initiative" class="max-w-6xl mx-auto px-4 py-8">

		<?php
		$ag_campaign = new WP_Query( array(
			'post_type'      => 'campaign',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $ag_campaign->have_posts() ) :
			while ( $ag_campaign->have_posts() ) :
				$ag_campaign->the_post();
				?>
				<div class="bg-black border-2 border-hazard-yellow p-6 sm:p-8 relative">

					<span class="absolute -top-3 right-4 bg-hazard-yellow text-black font-display font-bold text-xs px-2.5 py-0.5 uppercase tracking-wide">
						AED Fund
					</span>

					<h2 class="font-display text-2xl font-bold uppercase mb-2 text-paper-light"><?php the_title(); ?></h2>
					<div class="text-zinc-400 text-xs sm:text-sm max-w-2xl mb-5 leading-relaxed"><?php the_excerpt(); ?></div>
					<?php get_template_part( 'template-parts/campaign-progress', null, array( 'post_id' => get_the_ID() ) ); ?>

				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		<?php else : ?>
			<p class="text-body text-zinc-400">No AED campaign yet — add one under AED Campaigns in wp-admin.</p>
		<?php endif; ?>

	</div>

</main>

<?php get_footer(); ?>
