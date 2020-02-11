<?php
/**
 * The template for displaying content in the single.php template
 *
 * Copied from Largo in order to comment out the hero image
 *
 * @since Largo 0.6.4
 * @since November 14, 2019 - date of last comparison to Largo trunk
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'hnews item' ); ?> itemscope itemtype="http://schema.org/Article">

	<?php do_action('largo_before_post_header'); ?>

	<header>

		<?php largo_maybe_top_term(); ?>

		<h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>
		<?php if ( $subtitle = get_post_meta( $post->ID, 'subtitle', true ) ) : ?>
			<h2 class="subtitle"><?php echo $subtitle ?></h2>
		<?php endif; ?>
		<h5 class="byline"><?php largo_byline( true, false, get_the_ID() ); ?></h5>

		<?php if ( ! of_get_option( 'single_social_icons' ) == false ) {
			largo_post_social_links();
		} ?>

<?php largo_post_metadata( $post->ID ); ?>

	</header><!-- / entry header -->

	<?php
		do_action('largo_after_post_header');

		// https://github.com/INN/umbrella-citylimits/pull/5
		// largo_hero(null,'span12');

		do_action('largo_after_hero');
	?>

	<?php get_sidebar(); ?>

	<section class="entry-content clearfix" itemprop="articleBody">

		<?php largo_entry_content( $post ); ?>

	</section>

	<?php do_action('largo_after_post_content'); ?>

</article>
