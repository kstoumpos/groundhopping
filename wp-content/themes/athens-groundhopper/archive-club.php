<?php
/**
 * Clubs archive.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">
	<div class="max-w-[1200px] mx-auto w-full px-8 py-12">

		<h1 class="font-display font-normal text-h1 text-paper-light mb-8">Clubs</h1>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<?php
				while ( have_posts() ) :
					the_post();
					$club_id   = get_the_ID();
					$ground_id = (int) get_post_meta( $club_id, 'ag_home_ground_id', true );
					?>
					<div class="ag-card p-6">
						<h3 class="font-label font-semibold text-h3 text-paper-light">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
						<?php if ( has_excerpt() || get_the_content() ) : ?>
							<div class="text-body-sm text-zinc-400 mt-2"><?php the_excerpt(); ?></div>
						<?php elseif ( $ground_id && get_post( $ground_id ) ) : ?>
							<p class="text-body-sm text-zinc-400 mt-2">Plays at <?php echo esc_html( get_the_title( $ground_id ) ); ?></p>
						<?php endif; ?>
					</div>
					<?php
				endwhile;
				?>
			</div>

			<div class="font-label text-label-sm uppercase text-zinc-400 mt-8">
				<?php
				the_posts_pagination( array(
					'prev_text' => '&larr; Previous',
					'next_text' => 'Next &rarr;',
				) );
				?>
			</div>
			<?php
		else :
			?>
			<p class="text-body text-zinc-400">No clubs added yet.</p>
			<?php
		endif;
		?>

	</div>
</main>

<?php get_footer(); ?>
