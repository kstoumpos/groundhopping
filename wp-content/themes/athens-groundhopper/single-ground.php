<?php
/**
 * Single Ground.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">

	<?php
	while ( have_posts() ) :
		the_post();
		$ground_id = get_the_ID();
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

			<div class="text-body text-zinc-400">
				<?php the_content(); ?>
			</div>

			<div>

				<div class="ag-card p-6 mb-6">
					<h3 class="font-label font-semibold text-label-lg uppercase text-paper-light mb-4">Ground facts</h3>
					<dl class="text-body-sm text-zinc-400 flex flex-col gap-2">

						<?php $address = get_post_meta( $ground_id, 'ag_address', true ); ?>
						<?php if ( $address ) : ?>
							<div class="flex justify-between gap-4"><dt>Address</dt><dd class="text-right text-paper-light"><?php echo esc_html( $address ); ?></dd></div>
						<?php endif; ?>

						<?php $surface = get_post_meta( $ground_id, 'ag_surface', true ); ?>
						<?php if ( $surface ) : ?>
							<div class="flex justify-between gap-4"><dt>Surface</dt><dd class="text-paper-light"><?php echo esc_html( 'artificial_turf' === $surface ? 'Artificial turf' : ucfirst( $surface ) ); ?></dd></div>
						<?php endif; ?>

						<?php $length = get_post_meta( $ground_id, 'ag_length_m', true ); $width = get_post_meta( $ground_id, 'ag_width_m', true ); ?>
						<?php if ( $length && $width ) : ?>
							<div class="flex justify-between gap-4"><dt>Dimensions</dt><dd class="text-paper-light"><?php echo esc_html( $length . ' × ' . $width . ' m' ); ?></dd></div>
						<?php endif; ?>

						<?php $capacity = get_post_meta( $ground_id, 'ag_capacity', true ); ?>
						<?php if ( '' !== $capacity ) : ?>
							<div class="flex justify-between gap-4"><dt>Capacity</dt><dd class="text-paper-light"><?php echo esc_html( number_format_i18n( (int) $capacity ) ); ?></dd></div>
						<?php endif; ?>

						<?php
						$facilities = array(
							'Changing rooms' => get_post_meta( $ground_id, 'ag_has_changing_rooms', true ),
							'Lighting'       => get_post_meta( $ground_id, 'ag_has_lighting', true ),
							'Stands'         => get_post_meta( $ground_id, 'ag_has_stands', true ),
						);
						foreach ( $facilities as $label => $value ) :
							if ( '' === $value ) {
								continue; // not recorded — say nothing rather than imply "no"
							}
							?>
							<div class="flex justify-between gap-4">
								<dt><?php echo esc_html( $label ); ?></dt>
								<dd class="<?php echo 'yes' === $value ? 'text-spray-neon' : 'text-zinc-500'; ?>"><?php echo 'yes' === $value ? 'Yes' : 'No'; ?></dd>
							</div>
							<?php
						endforeach;
						?>

						<?php $union = get_post_meta( $ground_id, 'ag_union', true ); ?>
						<?php if ( $union ) : ?>
							<div class="flex justify-between gap-4"><dt>Union</dt><dd class="text-paper-light"><?php echo esc_html( ag_union_label( $union ) ); ?></dd></div>
						<?php endif; ?>

						<div class="flex justify-between gap-4 pt-2 border-t border-zinc-800">
							<dt>AED</dt>
							<dd class="text-paper-light"><?php echo esc_html( ag_aed_status_label( get_post_meta( $ground_id, 'ag_aed_status', true ) ) ); ?></dd>
						</div>

					</dl>
				</div>

				<?php
				$ag_campaign = new WP_Query( ag_ground_campaign_args( $ground_id ) );
				if ( $ag_campaign->have_posts() ) :
					while ( $ag_campaign->have_posts() ) :
						$ag_campaign->the_post();
						?>
						<div class="ag-card p-6">
							<h3 class="font-label font-semibold text-label-lg uppercase text-paper-light mb-4">AED fund</h3>
							<?php get_template_part( 'template-parts/campaign-progress', null, array( 'post_id' => get_the_ID() ) ); ?>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<p class="text-body-sm text-zinc-400">No AED campaign running for this ground yet.</p>
					<?php
				endif;
				?>
			</div>

		</div>

		<?php
	endwhile;
	?>

</main>

<?php get_footer(); ?>
