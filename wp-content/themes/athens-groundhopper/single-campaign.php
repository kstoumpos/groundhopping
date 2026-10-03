<?php
/**
 * Single AED Campaign.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="max-w-[1200px] mx-auto w-full px-8 pt-8">
				<?php the_post_thumbnail( 'large', array( 'class' => 'w-full aspect-[16/6] object-cover border border-zinc-800' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="max-w-[1200px] mx-auto w-full px-8 pt-8">
			<h1 class="font-display font-normal text-h1 text-paper-light"><?php the_title(); ?></h1>
		</div>

		<div class="max-w-[1200px] mx-auto w-full px-8 py-10 grid grid-cols-1 md:grid-cols-[1fr_360px] gap-12 items-start">

			<div>
				<h2 class="font-label font-semibold text-h3 text-paper-light mb-4">Why this ground needs an AED</h2>
				<div class="text-body text-zinc-400"><?php the_content(); ?></div>
			</div>

			<div class="ag-card p-6 sticky top-24">

				<?php get_template_part( 'template-parts/campaign-progress', null, array( 'post_id' => get_the_ID() ) ); ?>

				<p class="font-label font-semibold text-label-sm uppercase text-zinc-400 mt-6 mb-3">Choose an amount</p>

				<!--
					The four buttons below are presentational only — wiring them to
					an actual charge (Viva Wallet / Stripe, still to be decided; see
					the data-sourcing research) is the next build step, not part of
					this theme scaffold. A small script would set the hidden amount
					field and submit to the payment provider's checkout.
				-->
				<div class="grid grid-cols-2 gap-2 mb-3">
					<button type="button" class="font-sans font-semibold text-body py-4 bg-zinc-900 text-paper-light border border-zinc-800">&euro;10</button>
					<button type="button" aria-pressed="true" class="font-sans font-semibold text-body py-4 bg-amf-red text-white border border-amf-red">&euro;25</button>
					<button type="button" class="font-sans font-semibold text-body py-4 bg-zinc-900 text-paper-light border border-zinc-800">&euro;50</button>
					<button type="button" class="font-sans font-semibold text-body py-4 bg-zinc-900 text-paper-light border border-zinc-800">Other</button>
				</div>

			</div>

		</div>

		<?php
	endwhile;
	?>

</main>

<?php get_footer(); ?>
