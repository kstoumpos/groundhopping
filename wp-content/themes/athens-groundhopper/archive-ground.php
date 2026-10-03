<?php
/**
 * Grounds archive.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">
	<div class="max-w-[1200px] mx-auto w-full px-8 py-12">

		<h1 class="font-display font-normal text-h1 text-paper-light mb-8">Grounds</h1>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div class="ag-card flex flex-col">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-full aspect-video object-cover' ) ); ?>
						<?php endif; ?>
						<div class="p-6">
							<h3 class="font-label font-semibold text-h3 text-paper-light">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<?php if ( has_excerpt() || get_the_content() ) : ?>
								<div class="text-body-sm text-zinc-400 mt-2"><?php the_excerpt(); ?></div>
							<?php else : ?>
								<?php
								$ground_id = get_the_ID();
								$address   = get_post_meta( $ground_id, 'ag_address', true );
								$surface   = get_post_meta( $ground_id, 'ag_surface', true );
								$capacity  = get_post_meta( $ground_id, 'ag_capacity', true );
								$bits      = array_filter( array(
									$address,
									$surface ? ( 'artificial_turf' === $surface ? 'Artificial turf' : ucfirst( $surface ) ) : '',
									'' !== $capacity ? number_format_i18n( (int) $capacity ) . ' capacity' : '',
								) );
								?>
								<?php if ( $bits ) : ?>
									<p class="text-body-sm text-zinc-400 mt-2"><?php echo esc_html( implode( ' · ', $bits ) ); ?></p>
								<?php endif; ?>
							<?php endif; ?>
						</div>
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
			<p class="text-body text-zinc-400">No grounds added yet.</p>
			<?php
		endif;
		?>

	</div>
</main>

<?php get_footer(); ?>
