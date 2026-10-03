<?php
/**
 * Template part: AED campaign progress bar, amount and Donate button.
 *
 * Classic-theme replacement for the old campaign-progress block — same
 * logic, just a plain include instead of a server-rendered block. Call it
 * with a post ID via the $args param (WP 5.5+):
 *   get_template_part( 'template-parts/campaign-progress', null, array( 'post_id' => $id ) );
 * $args is automatically available inside the included file.
 *
 * @package Athens_Groundhopper
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();

if ( ! $post_id || 'campaign' !== get_post_type( $post_id ) ) {
	return;
}

$goal    = (float) get_post_meta( $post_id, 'ag_goal_amount', true );
$raised  = (float) get_post_meta( $post_id, 'ag_raised_amount', true );
$status  = get_post_meta( $post_id, 'ag_status', true );
$percent = $goal > 0 ? min( 100, round( ( $raised / $goal ) * 100 ) ) : 0;
?>
<div class="ag-campaign-progress">

	<?php if ( 'funded' === $status || 'installed' === $status ) : ?>
		<span class="ag-pill inline-block font-label font-semibold text-label-sm uppercase tracking-[0.4px] text-white bg-amf-red px-3 py-1 mb-3">
			<?php echo 'installed' === $status ? 'AED installed' : 'Fully funded'; ?>
		</span>
	<?php endif; ?>

	<div class="ag-progress-track" role="progressbar"
		aria-valuenow="<?php echo esc_attr( $percent ); ?>"
		aria-valuemin="0"
		aria-valuemax="100"
		aria-label="<?php esc_attr_e( 'Funds raised toward this ground&#8217;s AED', 'athens-groundhopper' ); ?>">
		<div class="ag-progress-fill" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
	</div>

	<div class="flex justify-between mt-2 font-sans text-caption text-zinc-400">
		<span><?php echo esc_html( ag_format_eur( $raised ) ); ?> raised of <?php echo esc_html( ag_format_eur( $goal ) ); ?></span>
		<span><?php echo esc_html( $percent ); ?>%</span>
	</div>

	<?php if ( 'active' === $status || ! $status ) : ?>
		<a class="ag-btn-primary w-full mt-4" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			Support this ground
		</a>
	<?php endif; ?>

</div>
