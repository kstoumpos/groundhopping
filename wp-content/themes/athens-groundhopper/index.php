<?php
/**
 * Fallback template — used when no more specific template matches
 * (e.g. a default WP page, a search results page, a post).
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">
	<div class="max-w-[1200px] mx-auto w-full px-8 py-12">

		<?php if ( have_posts() ) : ?>

			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'mb-12' ); ?>>
					<h2 class="font-display text-h2 text-paper-light">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="text-body text-zinc-400 mt-2"><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>

			<div class="font-label text-label-sm uppercase text-zinc-400">
				<?php the_posts_pagination(); ?>
			</div>

		<?php else : ?>

			<p class="text-body text-zinc-400">Nothing found.</p>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
