<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="bg-concrete-dark">

	<!-- Hazard-tape ticker: a genuine CSS marquee (duplicated track,
	     scrolled exactly -50%) so the loop has no visible seam. -->
	<div class="bg-hazard-yellow text-black font-display font-bold text-xs uppercase tracking-widest py-1.5 border-b-2 border-black overflow-hidden whitespace-nowrap">
		<div class="flex w-max animate-marquee">
			<div class="flex gap-8 pr-8">
				<span>&#9733; GROUNDHOPPING.GR // THE INDEPENDENT ATHENS FOOTBALL RADAR &#9733;</span>
				<span>KEEP IT NEUTRAL ON METRO &amp; PUBLIC TRANSIT</span>
				<span>100% MERCH PROFITS TO GRASSROOTS DEFIBRILLATORS</span>
				<span>SUPPORT BOTH MEN'S EPSA &amp; WOMEN'S LEAGUES (WFL)</span>
				<span>RESPECT THE NEIGHBORHOODS &amp; LOCAL TERRACES</span>
				<span>&#9733; GROUNDHOPPING.GR // AGAINST MODERN FOOTBALL &#9733;</span>
			</div>
			<!-- duplicate track, aria-hidden: this second copy is what makes the -50% loop seamless -->
			<div class="flex gap-8 pr-8" aria-hidden="true">
				<span>&#9733; GROUNDHOPPING.GR // THE INDEPENDENT ATHENS FOOTBALL RADAR &#9733;</span>
				<span>KEEP IT NEUTRAL ON METRO &amp; PUBLIC TRANSIT</span>
				<span>100% MERCH PROFITS TO GRASSROOTS DEFIBRILLATORS</span>
				<span>SUPPORT BOTH MEN'S EPSA &amp; WOMEN'S LEAGUES (WFL)</span>
				<span>RESPECT THE NEIGHBORHOODS &amp; LOCAL TERRACES</span>
				<span>&#9733; GROUNDHOPPING.GR // AGAINST MODERN FOOTBALL &#9733;</span>
			</div>
		</div>
	</div>

	<?php
	/**
	 * This nav is hand-typed HTML, not wp_nav_menu(): its first two links
	 * are same-page anchors (#radar, #ticket-rules) into front-page.php's
	 * sections, which only resolve correctly ON the homepage — exactly the
	 * original design's intent. A 'primary' menu location is already
	 * registered in functions.php; once there are real separate pages for
	 * each section, swap this block for:
	 *   wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false ) )
	 */
	?>
	<div class="sticky top-0 z-50 bg-concrete-dark border-b-2 border-paper-light">
		<div class="max-w-6xl mx-auto px-4 py-3.5 flex justify-between items-center flex-wrap gap-4">
			<div class="flex items-center gap-3">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bg-amf-red text-white font-display font-bold px-2.5 py-1 text-base border-2 border-white shadow-[3px_3px_0px_#fff] -rotate-1 inline-block">
					GROUNDHOPPING<span class="text-hazard-yellow">.GR</span>
				</a>
				<span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider hidden md:inline">
					Athens Concrete &amp; Terrace Archive
				</span>
			</div>

			<div class="flex items-center gap-4">
				<nav class="flex gap-4 text-xs sm:text-sm font-semibold uppercase">
					<a href="/#radar" class="hover:bg-paper-light hover:text-black px-1.5 py-0.5 transition">Weekend Radar</a>
					<a href="/#ticket-rules" class="hover:bg-paper-light hover:text-black px-1.5 py-0.5 transition">Tickets &amp; Law</a>
					<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class="hover:bg-paper-light hover:text-black px-1.5 py-0.5 transition">AED Fund</a>
				</nav>
				<span class="text-xs font-bold border border-zinc-700 px-2 py-0.5 text-spray-neon">EN / GR</span>
			</div>
		</div>
	</div>

</header>
