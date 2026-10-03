<?php
/**
 * Single Club.
 *
 * @package Athens_Groundhopper
 */

get_header();
?>

<main class="bg-concrete-dark min-h-screen">

	<?php
	while ( have_posts() ) :
		the_post();
		$club_id = get_the_ID();
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

			<div class="ag-card p-6">
				<h3 class="font-label font-semibold text-label-lg uppercase text-paper-light mb-4">Club info</h3>
				<dl class="text-body-sm text-zinc-400 flex flex-col gap-2">

					<?php
					$ground_id = (int) get_post_meta( $club_id, 'ag_home_ground_id', true );
					if ( $ground_id && get_post( $ground_id ) ) :
						?>
						<div class="flex justify-between gap-4">
							<dt>Home ground</dt>
							<dd class="text-right"><a class="text-spray-neon hover:underline" href="<?php echo esc_url( get_permalink( $ground_id ) ); ?>"><?php echo esc_html( get_the_title( $ground_id ) ); ?></a></dd>
						</div>
						<?php
					endif;

					$address = get_post_meta( $club_id, 'ag_address', true );
					if ( $address ) :
						?>
						<div class="flex justify-between gap-4"><dt>Address</dt><dd class="text-right text-paper-light"><?php echo esc_html( $address ); ?></dd></div>
						<?php
					endif;

					$email = get_post_meta( $club_id, 'ag_email', true );
					if ( $email ) :
						?>
						<div class="flex justify-between gap-4"><dt>Email</dt><dd class="text-right"><a class="text-spray-neon hover:underline" href="<?php echo esc_attr( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></dd></div>
						<?php
					endif;

					$gga = get_post_meta( $club_id, 'ag_gga_code', true );
					if ( $gga ) :
						?>
						<div class="flex justify-between gap-4"><dt>ΓΓΑ code</dt><dd class="text-paper-light"><?php echo esc_html( $gga ); ?></dd></div>
						<?php
					endif;

					$union = get_post_meta( $club_id, 'ag_union', true );
					if ( $union ) :
						?>
						<div class="flex justify-between gap-4 pt-2 border-t border-zinc-800"><dt>Union</dt><dd class="text-paper-light"><?php echo esc_html( ag_union_label( $union ) ); ?></dd></div>
						<?php
					endif;
					?>

				</dl>
			</div>

		</div>

		<?php
	endwhile;
	?>

</main>

<?php get_footer(); ?>
