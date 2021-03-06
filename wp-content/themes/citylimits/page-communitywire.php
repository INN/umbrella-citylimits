<?php
/**
 * Page Template: CommunityWire
 * Template Name: CommunityWire
 * Description: Custom landing page
 */

global $shown_ids, $post;

/*
 * Establish some common query parameters
 */
$features = get_the_terms( $post->ID, 'series' );
// we're going to assume that the series landing page is in no more than one series, because that's how you're *supposed* to do it.
$series = $features[0];
$project_tax_query = array(
		'taxonomy' => 'series',
		'terms' => $series->term_id,
		'field' => 'ID',
	);

// begin the page rendering

// This is the rezone-specific header, /header-rezone.php
get_header();
wp_enqueue_script( 'cl-newsletter' );

?>


<section class="rezone-overview">
	<div class="row-fluid">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1 class="hidden-page-title"><?php the_title(); ?></h1>
		</div>
		<div class="row-fluid">
			<?php the_content(); ?>
		<?php endwhile; ?>
		<div class="row-fluid listing-widgets">
			<?php
				do_action('largo_after_post_header');

				largo_hero(null,'span12');

				do_action('largo_after_hero');
			?>

				<?php dynamic_sidebar( 'communitywire-listings' ); ?>
		</div>
	</div>
</section>

<?php get_footer();
