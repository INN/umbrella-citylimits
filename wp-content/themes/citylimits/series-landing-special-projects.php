<?php
/**
 * Template Name: Series Landing Page - Special Projects
 * Description: The special-projects template for a series landing page.
 */
get_header();

// Load up our meta data and whatnot
the_post();

//make sure it's a landing page.
if ( 'cftl-tax-landing' == $post->post_type ) {
	$opt = get_post_custom( $post->ID );
	foreach( $opt as $key => $val ) {
		$opt[ $key ] = $val[0];
	}
	$opt['show'] = maybe_unserialize($opt['show']);	//make this friendlier
	if ( 'all' == $opt['per_page'] ) $opt['per_page'] = -1;
	/**
	 * $opt will look like this:
	 *
	 *	Array (
	 *		[header_enabled] => boolean
	 *		[show_series_byline] => boolean
	 *		[show_sharebar] => boolean
	 *		[header_style] => standard|alternate
	 *		[cftl_layout] => one-column|two-column|three-column
	 *		[per_page] => integer|all
	 *		[post_order] => ASC|DESC|top, DESC|top, ASC
	 *		[footer_enabled] => boolean
	 *		[footerhtml] => {html}
	 *		[show] => array with boolean values for keys byline|excerpt|image|tags
	 *	)
	 *
	 * The post description is stored in 'excerpt' and the custom HTML header is the post content
	 */
}

// #content span width helper
$content_span = array( 'one-column' => 12, 'two-column' => 8, 'three-column' => 5 );
?>

<?php if ( $opt['header_enabled'] ) { ?>
	<div class="series-banner">
		<?php the_post_thumbnail( 'large' ); ?>
	</div>
	<section id="series-header" class="">
		<span class="special-project"><?php esc_html_e( 'Special Project', 'citylimits' ); ?></span>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php
			if ( $opt['show_series_byline'] ) {
				echo '<h5 class="byline">' . largo_byline( false, false, get_the_ID() ) . '</h5>';
			}
		?>
			<div class="description">
				<?php echo apply_filters( 'the_content', $post->post_excerpt ); ?>
			</div>
	</section>
</div><!-- end main div -->

<?php
	if ( 'standard' != $opt['header_style'] ) {
		// these classes are a bit of a hack; there's some classes in Largo that assume body.single.normal instead of just .normal; I've tried to patch them in less/series-landing-pages-special-projects.less
		echo '<section class=" normal entry-content">';
			the_content();
		echo '</section>';
	}
?>

<?php
	if ( $opt['show_sharebar'] ) {
		largo_post_social_links();
	}
?>

<?php 
	if ( $opt['cftl_secondary_navigation'] ) {
		echo '<div id="secondary-navigation-menu-container">';
		?>
		<ul id="secondary-navigation-menu-mobile">
			<li>
				<!-- "hamburger" button (3 bars) to trigger off-canvas navigation -->
				<a class="btn btn-navbar toggle-secondary-nav-bar" title="<?php esc_attr_e('More', 'largo'); ?>">
					<div class="bars">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</div>
					<div class="close">
						<span class="dashicons dashicons-no-alt" aria-label="close menu"></span>
					</div>
					<span><?php echo the_title(); ?></span>
				</a>
			</li>
		</ul>
		<?php
			echo '<ul class="secondary-navigation-menu">';
				$args = array(
					'container' => true,
					'items_wrap' => '%3$s',
					'menu_class' => 'secondary-navigation-menu',
					'menu' => $opt['cftl_secondary_navigation'],
					'walker' => new Bootstrap_Walker_Nav_Menu()
				);
				largo_nav_menu( $args );
			echo '</ul>';
		echo '</div>';
	}
?>

<div id="series-main" class="row-fluid clearfix">
<?php } // end the "if there's a header" conditional ?>

<?php // display left rail
if ( 'three-column' == $opt['cftl_layout'] ) {
	$left_rail = $opt['left_region'];
	?>
		<aside id="sidebar-left" class="clearfix">
			<div class="widget-area" role="complementary">
				<?php
					dynamic_sidebar($left_rail);
				?>
			</div>
		</aside>
	<?php
}
?>

<div id="content" class="span<?php echo $content_span[ $opt['cftl_layout'] ]; ?> stories" role="main">
<?php do_action( 'city_limits_special_projects_series_before_stories' ); ?>

</div><!-- /.grid_8 #content -->

<?php // display left rail
if ($opt['cftl_layout'] != 'one-column') {
	if (!empty($opt['right_region']) && $opt['right_region'] !== 'none') {
		$right_rail = $opt['right_region'];
	} else {
		$right_rail = 'single';
	}
	?>
	<aside id="sidebar" class="clearfix">
		<?php do_action('largo_before_sidebar_content'); ?>
		<div class="widget-area" role="complementary">
			<?php
				do_action('largo_before_sidebar_widgets');
				dynamic_sidebar($right_rail);
				do_action('largo_after_sidebar_widgets');
			?>
		</div><!-- .widget-area -->
		<?php do_action('largo_after_sidebar_content'); ?>
	</aside>

	<?php
}

//display series footer
if ( 'none' != $opt['footer_style'] ) {
	?>
		<section id="series-footer">
			<?php
				/*
				 * custom footer html
				 * If we don't reset the post meta here, then the footer HTML is from the wrong post. This doesn't mess with LMP, because it happens after LMP is enqueued in the main column.
				 */
				wp_reset_postdata();
				if ( 'custom' == $opt['footer_style']) {
					echo apply_filters( 'the_content', $opt['footerhtml'] );
				} else if ( 'widget' == $opt['footer_style'] && is_active_sidebar( largo_make_slug( $post->post_title )."_footer" ) ) { ?>
					<aside id="sidebar-bottom">
					<?php dynamic_sidebar( largo_make_slug( $post->post_title )."_footer" ); ?>
					</aside>
				<?php }
			?>
		</section>
	<?php
} ?>

<!-- /.grid_4 -->
<?php get_footer();
