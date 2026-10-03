<?php
/**
 * Template part: a complete Weekend Radar fixture card — tag, title,
 * ground + kickoff line, vibe note, transit tip, and a CTA to the ground.
 *
 * Classic-theme replacement for the old fixture-card-meta block. Renders
 * its own title rather than relying on the_title() separately, because the
 * title needs to sit between the tag row and the location line. Call it
 * with a post ID via $args:
 *   get_template_part( 'template-parts/fixture-card', null, array( 'post_id' => $id ) );
 *
 * Simplification worth knowing: the card's own border colour (wfl-purple
 * for a women's fixture in the original mockup) is NOT varied per post
 * here — only the tag pill and the CTA button change colour by
 * competition. The card border stays a uniform zinc-800 for all fixtures.
 * Ask if you want the border to vary too.
 *
 * @package Athens_Groundhopper
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();

if ( ! $post_id || 'fixture' !== get_post_type( $post_id ) ) {
	return;
}

$door_price   = get_post_meta( $post_id, 'ag_door_price', true );
$vibe_note    = get_post_meta( $post_id, 'ag_vibe_note', true );
$transit_note = get_post_meta( $post_id, 'ag_transit_note', true );
$ground_id    = (int) get_post_meta( $post_id, 'ag_ground_id', true );

$terms     = get_the_terms( $post_id, 'competition' );
$term      = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
$tag_label = $term ? $term->name : 'Fixture';
$tag_slug  = $term ? $term->slug : '';
$tag_class = ag_competition_pill_classes( $tag_slug );
$is_womens = ( false !== strpos( $tag_slug, 'women' ) || false !== strpos( $tag_slug, 'wfl' ) );
$cta_class = $is_womens
	? 'bg-wfl-purple text-white hover:bg-purple-500'
	: 'bg-paper-light text-black hover:bg-white';

$ground_name = $ground_id ? get_the_title( $ground_id ) : '';
$ground_link = $ground_id ? get_permalink( $ground_id ) : '';
$kickoff     = get_the_date( 'D H:i', $post_id ); // post_date IS kickoff, by convention
?>
<div class="ag-fixture-card">

	<div class="flex justify-between items-start mb-2">
		<span class="<?php echo esc_attr( $tag_class ); ?> text-[10px] font-bold px-1.5 py-0.5 uppercase">
			<?php echo esc_html( $tag_label ); ?>
		</span>
		<?php if ( $door_price ) : ?>
			<span class="text-[10px] text-zinc-400">Door: <?php echo esc_html( $door_price ); ?></span>
		<?php endif; ?>
	</div>

	<h3 class="font-display text-lg font-bold mt-1 mb-0.5 text-paper-light">
		<a class="hover:text-hazard-yellow" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
	</h3>

	<?php if ( $ground_name ) : ?>
		<p class="text-xs text-zinc-400 mb-3">
			&#128205; <?php echo esc_html( $ground_name ); ?> | <?php echo esc_html( $kickoff ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $vibe_note ) : ?>
		<div class="bg-black border-l-2 border-hazard-yellow p-2.5 text-xs text-zinc-300 mb-3">
			<strong>Vibe:</strong> <?php echo esc_html( $vibe_note ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $transit_note ) : ?>
		<div class="text-[11px] text-zinc-400 border-t border-zinc-800 pt-2 mb-3">
			&#128652; <strong>Transit:</strong> <?php echo esc_html( $transit_note ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $ground_link ) : ?>
		<a class="block text-center w-full font-display font-bold text-xs uppercase py-2 transition <?php echo esc_attr( $cta_class ); ?>"
			href="<?php echo esc_url( $ground_link ); ?>">
			Ground Guide &amp; Area Tips &rarr;
		</a>
	<?php endif; ?>

</div>
