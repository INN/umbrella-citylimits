<?php

define( 'SHOW_STICKY_NAV', false );
define( 'SHOW_CATEGORY_RELATED_TOPICS', false );

// Setup some contants we'll need in various places.
define( 'LARGO_EXT_DIR', dirname( __FILE__ ) );
define( 'LARGO_EXT', __FILE__ );


/**
 * Include theme files
 *
 * Based off of how Largo loads files: https://github.com/INN/Largo/blob/master/functions.php#L358
 *
 * 1. hook function Largo() on after_setup_theme
 * 2. function Largo() runs Largo::get_instance()
 * 3. Largo::get_instance() runs Largo::require_files()
 *
 * This function is intended to be easily copied between child themes, and for that reason is not prefixed with this child theme's normal prefix.
 *
 * @link https://github.com/INN/Largo/blob/master/functions.php#L145
 */
function largo_child_require_files() {
	$includes = array(
		'/inc/ajax-functions.php',
		'/inc/registration.php',
		'/inc/term-meta.php',
		'/inc/metaboxes.php',
		'/inc/post-templates.php',
		'/inc/enqueue.php',
		'/inc/widgets/neighborhood-content.php',
		'/inc/widgets/zonein-events.php',
	);

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( is_plugin_active( 'wpjobboard/index.php' ) ) {
		$includes[] = '/inc/job-board.php';
	}
	if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
		$includes[] = '/inc/gravityforms/events-calendar.php';
	}

	foreach ( $includes as $include ) {
		require_once( get_stylesheet_directory() . $include );
	}

}
add_action( 'after_setup_theme', 'largo_child_require_files' );

/**
 * re-enable the default WP RSS widget.
 */
function citylimits_widgets_init() {
	register_widget( 'WP_Widget_RSS' );
	register_widget( 'neighborhood_content' );
	register_widget( 'zonein_events' );
}
add_action( 'widgets_init', 'citylimits_widgets_init', 11 );

/**
 * Set the number of posts in the right-hand side of the Top Stories homepage template to 2.
 *
 * Largo's default is 6. CityLimits does not want the "More headlines" area to appear, which appears if 4 or more posts are in the area.
 *
 * @return 2
 * @param int $showstories The number of stories.
 */
function citylimits_featured_stories_count( $showstories ) {
	return 3;
}
add_filter( 'largo_homepage_topstories_post_count', 'citylimits_featured_stories_count' );


/**
 * Taboola code
 */
function citylimits_taboola_header() {
	?>
	<script type="text/javascript">
		window._taboola = window._taboola || [];
		_taboola.push(
		{article:'auto'}
		);
		!function (e, f, u)
		{ e.async = 1; e.src = u; f.parentNode.insertBefore(e, f); }
		(document.createElement('script'),
		document.getElementsByTagName('script')[0],
		'//cdn.taboola.com/libtrc/citylimit/loader.js');
	</script>
	<?php
}
add_action( 'wp_head', 'citylimits_taboola_header' );


/**
 * Add Taboola's insertion script to the footer.
 */
function citylimits_taboola_footer() {
	?>
	<script type="text/javascript">
		window._taboola = window._taboola || [];
		_taboola.push(
		{flush: true}
		);
	</script>
	<?php
}
add_action( 'wp_footer', 'citylimits_taboola_footer' );


/* Custom registration link */
add_filter( 'register', function( $link ) {
	return '<a href="' . site_url( '/register' ) . '">Register</a>';
});


/**
 * Custom fields for user registration
 *
 * @param WP_Error A WP_Error object containing 'user_name' or 'user_email' errors.
 * @link https://developer.wordpress.org/reference/hooks/signup_extra_fields/
 * @hook signup_extra_fields
 */
function citylimits_custom_signup_fields_early( $values ) {
	?>
	<div class="form-group">
		<label for="organization"><?php esc_html_e( 'Organization name (optional)', 'citylimits' ); ?></label>
		<input type="text" value="<?php if ( ! empty( $values['organization'] ) ) {
			echo esc_attr( $values['organization'] );
		} ?>" name="organization">

		<?php
			$errmsg = $values['errors']->get_error_message( 'organization' );
			if ( $errmsg ) : ?>
			<p class="alert alert-error"><?php echo esc_html( $values['errmsg'] ); ?></p>
		<?php endif; ?>
	</div>

	<div class="form-group">
		<label>Want to receive our free newsletter? <a href="https://app.getresponse.com/site2/citylimits?u=Btt5L&webforms_id=439505">Sign up here.</a></label>
	</div>
	<?php
}
add_action( 'signup_extra_fields', 'citylimits_custom_signup_fields_early', 1, 2 );

/**
 * Add recaptcha to the error message
 */
function citylimits_custom_signup_fields_late( $values ) {
	$errmsg = $values->errors->get_error_message( 'recaptcha' );
	?>
	<div class="form-group">
		<label for="recaptcha_response_field"><?php esc_html_e( 'Are you human?', 'citylimits' ); ?></label>
		<?php
		/* ReCaptcha */
		require_once( 'lib/recaptchalib.php' );
		echo recaptcha_get_html( RECAPCHA_PUBLIC_KEY );
		if ( $errmsg ) {
			printf (
				'<p class="alert alert-error">%1$s</p>',
				esc_html( $errmsg )
			);
		}
		?>
	</div>
	<?php
}
add_action( 'signup_extra_fields', 'citylimits_custom_signup_fields_late', 10, 2 );

/**
 * Verify citylimits custom signup fields applied above.
 *
 * @param array $result The $_POST variables from the form to validate.
 * @param array $extras ??
 * @uses lib/recaptahlib.php
 */
function citylimits_verify_custom_signup_fields( $result, $extras = null ) {
	/* Check the reCaptcha */
	require_once( 'lib/recaptchalib.php' );
	$privatekey = RECAPCHA_PRIVATE_KEY;
	$remote_ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );

	$resp = recaptcha_check_answer(
		$privatekey,
		$remote_ip,
		$result['recaptcha_challenge_field'],
		$result['recaptcha_response_field']
	);

	/* Check the Captcha */
	if ( ! $resp->is_valid ) {
		$result['errors']->add( 'recaptcha',__( 'The entered captcha was incorrect','citylimits' ) );
		return $result;
	} else {
		return $result;
	}
}
add_action( 'largo_validate_user_signup_extra_fields', 'citylimits_verify_custom_signup_fields' );


/**
 * Add an organization field to the user profile
 *
 * @param WP_User $user the WordPress user object.
 */
function citylimits_user_profile_fields( $user ) {
	$organization = get_user_meta( $user->ID, 'organization', true );
	$mailing_id = get_user_meta( $user->ID, 'mailing_id', true );
	?>
	<h3>Other preferences</h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="organization"><?php esc_html_e( 'Organization name', 'citylimits' ); ?></label></th>
				<td><input type="text" value="<?php if ( ! empty( $organization ) ) {
					echo esc_attr( $organization );
				} ?>" name="organization"></td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action( 'show_user_profile',  'citylimits_user_profile_fields' );


/**
 * Save organization when saving the user's profile
 *
 * @param int $user_id The user ID.
 */
function citylimits_save_user_profile_fields( $user_id ) {
	if ( ! empty( $_POST ) ) {
		update_user_meta( $user_id, 'organization', esc_html( wp_unslash( $_POST['organization'] ) ) );
	}
}
add_action( 'personal_options_update', 'citylimits_save_user_profile_fields' );


/**
 * Unregister a widget
 */
function cl_widgets() {
	unregister_widget( 'TribeCountdownWidget' );
}
add_action( 'widgets_init', 'cl_widgets', 14 );


/* Remove the largo logo from login page */
add_action( 'init', function() {
	remove_action( 'login_head', 'largo_custom_login_logo' );
});


/**
 * Show City Limits logo on the WordPress login page
 */
function citylimits_custom_login_logo() {
	echo '<style type="text/css">
			.login h1 a {
				background-image: url(' . esc_url( get_stylesheet_directory_uri() ) . '/img/citylimits.png) !important;
				background-size: 200px 200px;
				height: 200px;
				width: 200px;
			}
		</style>';
}
add_action( 'login_head', 'citylimits_custom_login_logo' );


/**
 * Something about redirecting people to some other place!?
 */
function citylimits_login_redirect( $redirect_to, $request, $user ) {
	if ( isset( $_GET['redirect_to'] ) || isset( $_POST['redirect_to'] ) ) {
		return $redirect_to;
	} else {
		return home_url();
	}
}
add_filter( 'login_redirect', 'citylimits_login_redirect', 10, 3 );


/**
 * Filter the citylimits job board query to not display indeed.com links.
 *
 * @todo
 * @link https://www.vulnerability-lab.com/get_content.php?id=1940
 */
function citylimits_job_query( $select ) {
	$select->order("t1.is_featured DESC, t1.job_created_at DESC, t1.id DESC, IF(t1.company_url NOT LIKE '%indeed.com%', 1, 0) DESC, t1.id DESC");
	return $select;
}
add_filter( 'wpjb_jobs_query', 'citylimits_job_query', 1, 10 );


/**
 * Force to allow users to register
 */
function citylimits_users_can_register( $option ) {
	return true;
}
add_filter( 'pre_option_users_can_register', 'citylimits_users_can_register', 1, 10 );

/**
 * Citylimits Google Analytics with some specific custom variables
 */
function citylimits_google_analytics() {
	// don't track logged in users.
	if ( ! is_user_logged_in() ) { ?>
		<script>
			( function ( i, s, o, g, r, a, m ) {i['GoogleAnalyticsObject']=r;i[r]=i[r]|| function() {( i[r].q=i[r].q||[] ).push( arguments )},i[r].l=1*new Date();a=s.createElement( o ), m=s.getElementsByTagName( o )[0];a.async=1;a.src=g;m.parentNode.insertBefore( a, m )} )
			( window,document,'script','https://www.google-analytics.com/analytics.js','ga' );

			ga( 'create', 'UA-529003-1', 'auto' );

			<?php
			global $post, $wp_query;

			if ( is_singular() ) {
				if ( has_term( 'zonein', 'series' ) ) {
					echo "ga( 'set', 'contentGroup1', 'ZoneIn' );\n";
				} elseif ( 'page-neighborhoods.php' === get_page_template_slug() ) {
					echo "ga( 'set', 'contentGroup1', 'ZoneIn' );\n";
				}

				/*
				 * Content Group 2 "election 2017" in response to https://secure.helpscout.net/conversation/421881188/1229/?folderId=1259187
				 */
				if (
					has_term( '2017-election', 'series' )
					|| has_term( 'campaign-2017-newswire', 'post_tag' )
					|| has_term( 'democracys-timetable-campaign-2017-schedules', 'post_tag' )
					|| has_term( 'district-data', 'post_tag' )
					|| has_term( 'max-murphy-podcasts', 'category' )
					// below here are specific sections
					|| 20726 === $post->ID // https://citylimits.org/citizens-toolkit/
					|| 1750692 === $post->ID // https://citylimits.org/campaign-2017-candidate-debate-calendar/
					|| 1663505 === $post->ID // https://citylimits.org/politistat-2017/
					|| 1667230 === $post->ID // https://citylimits.org/our-2017-political-polls-vote-here-see-results/
					|| 1664887 === $post->ID // https://citylimits.org/lookback-dispatches-from-new-york-city-campaign-history/
					|| 1685102 === $post->ID // https://citylimits.org/a-users-guide-to-new-york-citys-elected-positions/
					|| 1663553 === $post->ID // https://citylimits.org/mayoral-race-2017/
					|| 1663554 === $post->ID // https://citylimits.org/council-races-2017/
				) {
					echo "ga( 'set', 'contentGroup2', 'election2017' );\n";
				} elseif ( 'page-neighborhoods.php' === get_page_template_slug() ) {
					echo "ga( 'set', 'contentGroup2', 'election2017' );\n";
				}
			} elseif ( is_tax() || is_archive() ) {
				$term = $wp_query->get_queried_object();
				if ( $term->name === 'ZoneIn' || $term->taxonomy === 'neighborhoods' ) {
					echo "ga( 'set', 'contentGroup1', 'ZoneIn' );\n";
				}
				if (
					'2017-election' === $term->slug
					|| 'campaign-2017-newswire' === $term->slug
					|| 'democracys-timetable-campaign-2017-schedules' === $term->slug
					|| 'district-data' === $term->slug
					|| 'max-murphy-podcasts' === $term->slug
				) {
					echo "ga( 'set', 'contentGroup2', 'election2017' );\n";
				}
			}
			?>

			ga( 'send', 'pageview' );
		</script>
		<?php
	}
}
add_action( 'wp_head', 'citylimits_google_analytics' );

/**
 * Remove the Constant Contact registration thing on the new-user page.
 */
function remove_cc_registration_filter() {
	global $pagenow;
	if ( $pagenow === 'user-new.php' ) {
		remove_filter( 'wpmu_signup_user_notification', 'constant_contact_register_post_multisite', 10 );
	}
}
add_action( 'plugins_loaded', 'remove_cc_registration_filter', 2 );

/**
 * Create the Neighborhoods taxonomy
 */
function create_neighborhoods_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Neighborhoods', 'Taxonomy General Name', 'citylimits' ),
		'singular_name'              => _x( 'Neighborhood', 'Taxonomy Singular Name', 'citylimits' ),
		'menu_name'                  => __( 'Neighborhoods', 'citylimits' ),
		'all_items'                  => __( 'All Neighborhoods', 'citylimits' ),
		'parent_item'                => __( 'Parent Neighborhood', 'citylimits' ),
		'parent_item_colon'          => __( 'Parent Neighborhood:', 'citylimits' ),
		'new_item_name'              => __( 'New Neighborhood', 'citylimits' ),
		'add_new_item'               => __( 'Add New Neighborhood', 'citylimits' ),
		'edit_item'                  => __( 'Edit Neighborhood', 'citylimits' ),
		'update_item'                => __( 'Update Neighborhood', 'citylimits' ),
		'view_item'                  => __( 'View Neighborhood', 'citylimits' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'neighborhoods', array( 'post' ), $args );

}
add_action( 'init', 'create_neighborhoods_taxonomy', 0 );

/**
 * Configuration for DFP plugin
 */
function citylimits_configure_dfp() {
	global $DoubleClick;

	$DoubleClick->networkCode = '1291657';

	/* BREAKpoints */
	$DoubleClick->register_breakpoint( 'phone', array( 'minWidth' => 0, 'maxWidth' => 769 ) );
	$DoubleClick->register_breakpoint( 'tablet', array( 'minWidth' => 769, 'maxWidth' => 980 ) );
	$DoubleClick->register_breakpoint( 'desktop', array( 'minWidth' => 980, 'maxWidth' => 9999 ) );
}
// add_action( 'dfw_setup', 'citylimits_configure_dfp' );

/**
 * Register the "ZoneIn" menu
 */
function register_zonein_menu() {
	register_nav_menu('zonein-menu',__( 'Zone In Menu' ));
}
add_action( 'init', 'register_zonein_menu' );

/**
 * Register sidebars for the neighborhood taxonomy
 */
function register_neighborhood_sidebars() {
	register_sidebar( array(
		'name'          => __( 'Neighborhoods Taxonomy Sidebar', 'citylimits' ),
		'id'            => 'rezone-neighborhoods-sidebar',
		'description'   => __( 'Widgets in this area will be shown on all neighborhood taxonomy pages' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>'
	) );

	register_sidebar( array(
		'name'          => __( 'Rezone Subpage', 'citylimits' ),
		'id'            => 'rezone-subpage-sidebar',
		'description'   => __( 'Widgets in this area will be shown on all neighborhood taxonomy pages' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>'
	) );
}
add_action( 'widgets_init', 'register_neighborhood_sidebars' );

/**
 * Register Custom Post Type
 */
function create_zonein_events_post_type() {

	$labels = array(
		'name'                  => 'ZoneIn Events',
		'singular_name'         => 'ZoneIn Event',
		'menu_name'             => 'ZoneIn Events',
		'name_admin_bar'        => 'ZoneIn Events',
		'archives'              => 'ZoneIn Events Archives',
		'all_items'             => 'All ZoneIn Events',
		'add_new_item'          => 'Add New ZoneIn Event',
	);
	$rewrite = array(
		'slug'                  => 'zonein-events',
		'with_front'            => true,
		'pages'                 => true,
		'feeds'                 => true,
	);
	$args = array(
		'label'                 => 'ZoneIn Event',
		'description'           => 'Events for the ZoneIn Series',
		'labels'                => $labels,
		'supports'              => array(),
		'taxonomies'            => array( 'neighborhoods' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-calendar',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => $rewrite,
		'capability_type'       => 'page',
	);
	register_post_type( 'zonein_events', $args );
}
add_action( 'init', 'create_zonein_events_post_type', 0 );

/**
 * Add the event time to events
 */
function citylimits_print_event_time() {
	if ( 'zonein_events' === get_post_type() ) {
		$date = get_post_meta( get_the_ID(), 'event_information_date_time', true );
		if ( $date ) {
			echo '<span class="date">' . esc_html( date( 'F d, Y', $date ) ) . '</span> ';
			echo '<span class="time">' . esc_html( date( 'g:ia', $date ) ). '</span> ';
		}
	}
}
add_action( 'largo_after_post_header', 'citylimits_print_event_time' );

/**
 * Order the zonein events archive by the event date, not by the published date
 *
 * @see citylimits_modify_zonein_events_lmp_query
 * @param WP_Query $query the present archive query.
 */
function citylimits_modify_zonein_events_query( $query ) {

	if ( $query->is_main_query() && is_post_type_archive( 'zonein_events' ) ) {
		$query->set( 'meta_key', 'event_information_date_time' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'ASC' );
	}
	return $query;
}
add_action( 'pre_get_posts', 'citylimits_modify_zonein_events_query' );

/**
 * Order the zonein events archive LMP by the event date, not by the published date
 *
 * @see citylimits_modify_zonein_events_query
 * @param WP_Query $query the present LMP query.
 */
function citylimits_modify_zonein_events_lmp_query( $args ) {
	if ( $args['post_type'] === 'zonein_events' ) {
		$args['meta_key'] = 'event_information_date_time';
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'ASC';
	}
	return $args;
}
add_action( 'largo_lmp_args', 'citylimits_modify_zonein_events_lmp_query' );

/**
 * Filter the main WP_Query by neighborhood on neighborhood post types
 *
 * @param WP_Query $query the present query.
 */
function zonein_tax_archive_query( $query ) {
	if ( $query->is_archive() && isset( $query->query['post-type'] ) && isset( $_GET['neighborhood'] ) && ! empty( $_GET['neighborhood'] ) ) {
		$query->set( 'tax_query', array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'post-type',
				'field'    => 'slug',
				'terms'    => array( $query->query['post-type'] ),
			),
			array(
				'taxonomy' => 'neighborhoods',
				'field' => 'slug',
				'terms' => sanitize_key( $_GET['neighborhood'] ),
			),
		) );
		return $query;
	}
}
add_action( 'pre_get_posts', 'zonein_tax_archive_query', 1 );
