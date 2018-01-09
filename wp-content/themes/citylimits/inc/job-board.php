<?php
/**
 * Functions for hooking in various sidebars, stylesheets, etc for the wpjobboard plugin
 */

/**
 * Don't use WPJB css
 */
add_action( 'wpjb_inject_media', function( $media ) {
	$media['css'] = false;
	return $media;
});

/**
 * Filter the citylimits job board query to not display indeed.com links.
 */
function citylimits_job_query( $select ) {
	$select->order("t1.is_featured DESC, t1.job_created_at DESC, t1.id DESC, IF(t1.company_url NOT LIKE '%indeed.com%', 1, 0) DESC, t1.id DESC");
	return $select;
}
add_filter( 'wpjb_jobs_query', 'citylimits_job_query', 1, 10 );

/**
 * Utilities for loading default job types and categories
 */
function reset_job_categories_and_types() {
	$directory = get_stylesheet_directory();

	if ( file_exists( $directory . '/config.php' ) ) {
		include_once $directory . '/config.php';
	} else {
		return false;
	}

	$query = new Daq_Db_Query();
	$query->select( '*' )->from( 'Wpjb_Model_Category t1' );
	$categories = $query->execute();
	foreach ( $categories as $category ) {
		$category->delete();
	}

	$query = new Daq_Db_Query();
	$query->select( '*' )->from( 'Wpjb_Model_JobType t1' );
	$types = $query->execute();
	foreach ( $types as $type ) {
		$type->delete();
	}

	if ( ! empty( $wpjobboard_job_types ) ) {
		set_job_types( $wpjobboard_job_types );
	}
	if ( ! empty( $wpjobboard_categories ) ) {
		set_job_categories( $wpjobboard_categories );
	}

	return true;
}

/**
 * Utility function for creating WP Job Board categories, from an array.
 *
 * @uses Wpjb_Model_Category
 */
function set_job_categories( $categories ) {
	foreach ( $categories as $category_attrs ) {
		$cat = new Wpjb_Model_Category();
		foreach ( $category_attrs as $k => $v ) {
			$cat->set( $k, $v );
		}
		$cat->save();
	}
}

/**
 * Utility function for creating WP Job Board job types
 *
 * @uses Wpjb_Model_JobType
 */
function set_job_types( $types ) {
	foreach ( $types as $type_attrs ) {
		$jtype = new Wpjb_Model_JobType();
		foreach ( $type_attrs as $k => $v ) {
			$jtype->set( $k, $v );
		}
		$jtype->save();
	}
}

/**
 * Enqueue custom sidebar styles
 */
function largo_jobboard_enqueue() {
	wp_enqueue_style('job-board-styles', get_stylesheet_directory_uri() . '/css/job-board.css', false, '20170609', 'screen');
}
add_action('wp_enqueue_scripts', 'largo_jobboard_enqueue' );

/**
 * Register the jobboard-widgets sidebar
 */
function largo_jobboard_register_sidebar() {
	register_sidebar( array(
		'name' 			=> __( 'Job Board', 'largo' ),
		'description' 	=> __( 'A widget area on job board pages', 'largo' ),
		'id' 			=> 'jobboard-widgets',
		'before_widget' => '<aside id="%1$s" class="%2$s clearfix">',
		'after_widget' 	=> '</aside>',
		'before_title' 	=> '<h3 class="widgettitle">',
		'after_title' 	=> '</h3>',
	) );
}
add_action('widgets_init', 'largo_jobboard_register_sidebar');

/**
 * Output jobboard-widgets sidebar if we're on a WPJobBoard page AND jobboard-widgets is active.
 */
function largo_jobboard_output_sidebar() {
	if ( largo_is_job_page() && is_active_sidebar( 'jobboard-widgets' )) {
		dynamic_sidebar( 'jobboard-widgets' );
	}
}
add_action('largo_after_sidebar_widgets', 'largo_jobboard_output_sidebar');

/**
 * Tests if the current page is a part of the WPJobBoard plugin
 */
function largo_is_job_page() {
	$jobboardOptions = get_option('wpjb_config', NULL);

	if (is_array($jobboardOptions))
		$wpjb_page_ids = array($jobboardOptions['link_jobs'], $jobboardOptions['link_resumes']);
	else
		return false;

	if (is_singular()) {
		global $post;
		if (in_array($post->ID, $wpjb_page_ids))
			return true;
	}

	return false;
}

/**
 * Loads custom WPJobBoard templates
 */
function largo_load_wpjoboard_templates($frontend, $result) {
	$view = $frontend->controller->view;
	$view->addDir( LARGO_EXT_DIR . '/templates/job-board', true );
}
add_action('wpjb_front_pre_render', 'largo_load_wpjoboard_templates', 0, 2);

/**
 * Loads custom WPJobBoard widget templates
 */
function largo_load_wpjoboard_widget_templates($view) {
	$view->addDir( LARGO_EXT_DIR . '/templates/widgets', true );
	return $view;
}
add_filter('daq_widget_view', 'largo_load_wpjoboard_widget_templates', 10, 1);

/**
 * Customize job add page title
 */
function customize_job_add_page_title( $arg ) {
	$arg = trim( $arg );

	if ( 'Create Ad' === $arg ) {
		return __( 'Post a job', 'citylimits' );
	} else {
		return $arg;
	}
}
add_filter( 'wpjb_set_title', 'customize_job_add_page_title' );

