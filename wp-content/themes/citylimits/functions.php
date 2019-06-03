<?php
ini_set('display_errors','Off');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

define( 'SHOW_STICKY_NAV', false );
define( 'SHOW_CATEGORY_RELATED_TOPICS', false );

// Setup some contants we'll need in various places
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
		'/inc/communitywire.php',
		'/inc/registration.php',
		'/inc/term-meta.php',
		'/inc/metaboxes.php',
		'/inc/post-templates.php',
		'/inc/enqueue.php',
		'/inc/widgets/communitywire-announcements.php',
		'/inc/widgets/communitywire-sidebar.php',
		'/inc/widgets/neighborhood-content.php',
		'/inc/widgets/zonein-events.php',
		'/inc/widgets/jp-related-posts.php',
		'/inc/widgets/cl-newsletter-header.php',
	);

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
		$includes[] = '/inc/gravityforms/events-calendar.php';
	}

	foreach ( $includes as $include ) {
		require_once( get_stylesheet_directory() . $include );
	}

}
add_action( 'after_setup_theme', 'largo_child_require_files' );

// re-enable the default WP RSS widget
function citylimits_widgets_init() {
	register_widget( 'WP_Widget_RSS' );
	register_widget( 'neighborhood_content' );
	register_widget( 'zonein_events' );
	register_widget( 'communitywire_announcements' );
	register_widget( 'communitywire_sidebar' );
	register_widget( 'jp_cl_related_posts' );
	register_widget( 'cl_newsletter_header' );
}
add_action( 'widgets_init', 'citylimits_widgets_init', 11 );

/**
 * Set the number of posts in the right-hand side of the Top Stories homepage template to 2.
 *
 * Largo's default is 6. CityLimits does not want the "More headlines" area to appear, which appears if 4 or more posts are in the area.
 *
 * @return 2
 * @param int $showstories
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


/* Custom fields for user registration */
function citylimits_custom_signup_fields_early( $values ) {
	extract( $values );
	?>
	<div class="form-group">
		<label for="organization"><?php _e('Organization name (optional)', 'citylimits'); ?></label>
		<input type="text" value="<?php if (!empty($organization)) { echo $organization; } ?>" name="organization">

		<?php if ( $errmsg = $errors->get_error_message('organization') ) : ?>
			<p class="alert alert-error"><?php echo $errmsg; ?></p>
		<?php endif; ?>
	</div>

	<div class="form-group">
		<label>Want to receive our free newsletter? <a href="https://app.getresponse.com/site2/citylimits?u=Btt5L&webforms_id=439505">Sign up here.</a></label>
	</div>
	<?php
}
add_action( 'signup_extra_fields', 'citylimits_custom_signup_fields_early', 1, 2 );


function citylimits_custom_signup_fields_late( $values ) {
	extract( $values );
	?>
	<div class="form-group">
		<label for="recaptcha_response_field"><?php _e('Are you human?', 'citylimits'); ?></label>
		<?php
		/* ReCaptcha */
		require_once('lib/recaptchalib.php');
		echo recaptcha_get_html( RECAPCHA_PUBLIC_KEY );
		if ( $errmsg = $errors->get_error_message( 'recaptcha' ) ) : ?>
			<p class="alert alert-error"><?php echo $errmsg; ?></p>
		<?php endif; ?>
	</div>
	<?php
}
add_action('signup_extra_fields', 'citylimits_custom_signup_fields_late', 10, 2);


/**
 * Verify citylimits custom signup fields applied above.
 *
 * @param $result. array. The $_POST variables from the form to validate.
 * @param $extras. array. ??
 */
function citylimits_verify_custom_signup_fields( $result, $extras=null ) {
	/* Check the reCaptcha */
	require_once( 'lib/recaptchalib.php' );
	$privatekey = RECAPCHA_PRIVATE_KEY;

	$resp = recaptcha_check_answer(
		$privatekey, $_SERVER["REMOTE_ADDR"], $result["recaptcha_challenge_field"],
		$result["recaptcha_response_field"]);

	/* Check the Captcha */
	if (!$resp->is_valid) {
		$result['errors']->add( 'recaptcha',__( 'The entered captcha was incorrect','citylimits' ) );
		return $result;
	} else {
		return $result;
	}
}
add_action( 'largo_validate_user_signup_extra_fields', 'citylimits_verify_custom_signup_fields' );


function citylimits_user_profile_fields( $user ) {
	$organization = get_user_meta( $user->ID, 'organization', true );
	$mailing_id = get_user_meta( $user->ID, 'mailing_id', true );
	?>
	<h3>Other preferences</h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="organization"><?php _e( 'Organization name', 'citylimits' ); ?></label></th>
				<td><input type="text" value="<?php if ( ! empty( $organization ) ) { echo $organization; } ?>" name="organization"></td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action( 'show_user_profile',  'citylimits_user_profile_fields' );


function citylimits_save_user_profile_fields( $user_id ) {
	if ( ! empty( $_POST ) ) {
		update_user_meta( $user_id, 'organization', $_POST['organization'] );
	}
}
add_action( 'personal_options_update', 'citylimits_save_user_profile_fields' );


function cl_widgets() {
	unregister_widget( 'TribeCountdownWidget' );
}
add_action( 'widgets_init', 'cl_widgets', 14 );


/* Remove the largo logo from login page */
add_action( 'init', function() {
	remove_action( 'login_head', 'largo_custom_login_logo' );
});


/* Show City Limits logo on login page */
function citylimits_custom_login_logo() {
	echo '<style type="text/css">
			.login h1 a {
				background-image: url(' . get_stylesheet_directory_uri() . '/img/citylimits.png) !important;
				background-size: 200px 200px;
				height: 200px;
				width: 200px;
			}
		</style>';
}
add_action('login_head', 'citylimits_custom_login_logo');


function citylimits_login_redirect( $redirect_to, $request, $user ) {
	if ( isset( $_GET['redirect_to'] ) || isset( $_POST['redirect_to'] ) ) {
		return $redirect_to;
	} else {
		return home_url();
	}
}
add_filter( 'login_redirect', 'citylimits_login_redirect', 10, 3 );

function citylimits_users_can_register( $option ) {
	return true;
}
add_filter( 'pre_option_users_can_register', 'citylimits_users_can_register', 1, 10 );


function citylimits_google_analytics() {
	if ( ! is_user_logged_in() ) { // don't track logged in users ?>
		<script>
			( function ( i, s, o, g, r, a, m ) {i['GoogleAnalyticsObject']=r;i[r]=i[r]|| function() {( i[r].q=i[r].q||[] ).push( arguments )},i[r].l=1*new Date();a=s.createElement( o ), m=s.getElementsByTagName( o )[0];a.async=1;a.src=g;m.parentNode.insertBefore( a, m )} )
			( window,document,'script','https://www.google-analytics.com/analytics.js','ga' );

			ga( 'create', 'UA-529003-1', 'auto' );

			<?php
			global $post, $wp_query;

			if ( is_singular() ) {
				if ( has_term( 'zonein', 'series' ) or has_term( 'mapping-the-future', 'series' ) ) {
					echo "ga( 'set', 'contentGroup1', 'MappingTheFuture' );\n";
				} elseif ( 'page-neighborhoods.php' === get_page_template_slug() ) {
					echo "ga( 'set', 'contentGroup1', 'MappingTheFuture' );\n";
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
				if ( $term->name === 'ZoneIn' || $term->name === "Mapping the Future" || $term->taxonomy === 'neighborhoods' ) {
					echo "ga( 'set', 'contentGroup1', 'MappingTheFuture' );\n";
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


function remove_cc_registration_filter() {
	global $pagenow;
	if ( $pagenow == 'user-new.php' )
		remove_filter( 'wpmu_signup_user_notification', 'constant_contact_register_post_multisite', 10 );
}
add_action( 'plugins_loaded', 'remove_cc_registration_filter', 2 );


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

    $DoubleClick->networkCode = "1291657";

    /* breakpoints */
    $DoubleClick->register_breakpoint( 'phone', array( 'minWidth'=>0, 'maxWidth'=>769 ) );
    $DoubleClick->register_breakpoint( 'tablet', array( 'minWidth'=>769, 'maxWidth'=>980 ) );
    $DoubleClick->register_breakpoint( 'desktop', array( 'minWidth'=>980, 'maxWidth'=>9999 ) );

}
// add_action( 'dfw_setup', 'citylimits_configure_dfp' );

function register_zonein_menu() {
  register_nav_menu('zonein-menu',__( 'Mapping the Future Menu' ));
}
add_action( 'init', 'register_zonein_menu' );

function register_neighborhood_sidebars() {
	register_sidebar( array(
		'name'		=> __( 'Neighborhoods Taxonomy Sidebar', 'citylimits' ),
		'id'		=> 'rezone-neighborhoods-sidebar',
		'description'	=> __( 'Widgets in this area will be shown on all neighborhood taxonomy pages' ),
		'before_widget'	=> '<section id="%1$s" class="widget %2$s">',
		'after_widget'	=> '</section>',
		'before_title'	=> '<h2 class="widgettitle">',
		'after_title'	=> '</h2>'
	) );

	register_sidebar( array(
		'name'		=> __( 'Mapping The Future Subpage', 'citylimits' ),
		'id'		=> 'rezone-subpage-sidebar',
		'description'	=> __( 'Widgets in this area will be shown on all neighborhood taxonomy pages' ),
		'before_widget'	=> '<li id="%1$s" class="widget %2$s">',
		'after_widget'	=> '</li>',
		'before_title'	=> '<h2 class="widgettitle">',
		'after_title'	=> '</h2>'
	) );

	register_sidebar( array(
		'name'		=> __( 'CommunityWire Listings', 'citylimits' ),
		'id'		=> 'communitywire-listings',
		'description'	=> __( 'Widgets in this area will be shown on the CommunityWire listing page' ),
		'before_widget'	=> '<div class="span6">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h2 class="widgettitle">',
		'after_title'	=> '</h2>'
	) );

	register_sidebar( array(
		'name'		=> __( 'CommunityWire Sidebar Content', 'citylimits' ),
		'id'		=> 'communitywire-sidebar-content',
		'description'	=> __( 'Widgets in this area will be shown as part of the CommunityWire Widget' ),
		'before_widget'	=> '<aside class="widget">',
		'after_widget'	=> '</aside>',
		'before_title'	=> '<h3 class="widgettitle">',
		'after_title'	=> '</h3>'
	) );
}
add_action( 'widgets_init', 'register_neighborhood_sidebars' );

// Register Custom Post Type
function create_zonein_events_post_type() {

	$labels = array(
		'name'                  => 'Mapping the Future Events',
		'singular_name'         => 'Mapping the Future Event',
		'menu_name'             => 'Mapping the Future Events',
		'name_admin_bar'        => 'Mapping the Future Events',
		'archives'              => 'Mapping the Future Events Archives',
		'all_items'             => 'All Mapping the Future Events',
		'add_new_item'          => 'Add New Mapping the Future Event',
	);
	$rewrite = array(
		'slug'                  => 'zonein-events',
		'with_front'            => true,
		'pages'                 => true,
		'feeds'                 => true,
	);
	$args = array(
		'label'                 => 'Mapping the Future Event',
		'description'           => 'Events for the Mapping the Future Series',
		'labels'                => $labels,
		'supports'              => array( ),
		'taxonomies'            => array( 'neighborhoods' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'		=> 'dashicons-calendar',
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

function citylimits_print_event_time() {
	if ( 'zonein_events' == get_post_type() ) {
		$date = get_post_meta( get_the_ID(), 'event_information_date_time', true );
		if ( $date ) {
			echo '<span class="date">' . date( 'F d, Y', $date ) . '</span> ';
			echo '<span class="time">' . date( 'g:ia', $date ) . '</span> ';
		}
	}
}
add_action( 'largo_after_post_header', 'citylimits_print_event_time' );


/**
 * Order the zonein events archive by the event date, not by the published date
 * @see citylimits_modify_zonein_events_lmp_query
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
 * @see citylimits_modify_zonein_events_query
 */
function citylimits_modify_zonein_events_lmp_query( $args ) {
	if ( $args['post_type'] == 'zonein_events' ) {
		$args['meta_key'] = 'event_information_date_time';
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'ASC';
	}
	return $args;
}
add_action( 'largo_lmp_args', 'citylimits_modify_zonein_events_lmp_query' );

/**
 * Filter the main WP_Query by neighborhood on neighborhood post types
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

/* Custom query for event list widget*/
function tribe_custom_list_widget_events ( ){
     
    // uncoment the line below and fill in your custom args
    $args = array(
        // 'eventDisplay'=>'upcoming',
        // 'posts_per_page'=>-1,
        'tax_query'=> array(
            array(
                'taxonomy' => 'tribe_events_cat',
                'field' => 'slug',
                'terms' => 'communitywire-events'
            )
        )
    );
    // $args = array();
 
    $posts = tribe_get_events( $args );
 
    return $posts;    
}
 
add_filter( 'tribe_get_list_widget_events', 'tribe_custom_list_widget_events' );


/**
 * get other scripts
 */
function citylimits_communitywire_enqueue() {
 	if (is_page_template( 'page-communitywire.php' )) {
		wp_enqueue_script( 'inn-tools', get_stylesheet_directory_uri() . '/js/communitywire.js', array( 'jquery' ), '1.1', true );
	}
}
add_action( 'wp_enqueue_scripts', 'citylimits_communitywire_enqueue' );

function citylimits_newsletter_enqueue() {
	wp_enqueue_script( 'jscookies', get_stylesheet_directory_uri() . '/js/cookies.js', null, '1.1', true );

	wp_register_script( 'cl-newsletter', get_stylesheet_directory_uri() . '/js/newsletter.js', array( 'jquery', 'jscookies' ), null, true );
	wp_localize_script( 'cl-newsletter', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
	wp_enqueue_script( 'cl-newsletter' );
}
add_action( 'wp_enqueue_scripts', 'citylimits_newsletter_enqueue' );

/* need this to allow Gravity Forms to post to API */
add_filter( 'gform_webhooks_request_args', function ( $request_args, $feed ) {
    $request_url = rgars( $feed, 'meta/requestURL' );
    if ( strpos( $request_url, '{rest_api_url}' ) === 0 || strpos( $request_url, rest_url() ) === 0 ) {
        $request_args['headers']['Authorization'] = 'Basic ' . base64_encode( USERNAME_HERE . ':' . PASSWORD_HERE );
    }
 
    return $request_args;
}, 10, 2 );


add_filter( 'register_post_type_args', 'add_cpt_capability_organizer', 10, 2 );

function add_cpt_capability_organizer( $args, $post_type ) {
	// Make sure we're only modifying our desired post type.
	if ( 'tribe_organizer' != $post_type ) 
		return $args;
	$args['capability_type'] = 'post';
	$args['public'] = 1;
	return $args;
}

add_filter( 'register_post_type_args', 'add_cpt_capability_venue', 999, 2 );

function add_cpt_capability_venue( $args, $post_type ) {
	// Make sure we're only modifying our desired post type.
	if ( 'tribe_venue' != $post_type ) 
		return $args;
	$args['capability_type'] = 'post';
	$args['public'] = 1;
	return $args;
}

/* ADD HOTJAR TO HEADER */
add_action('wp_head', 'add_hotjar');
function add_hotjar() {
	print <<<EOH
<!-- Hotjar Tracking Code for citylimits.org -->
<script>
	(function(h,o,t,j,a,r){
		h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
		h._hjSettings={hjid:1295994,hjsv:6};
		a=o.getElementsByTagName('head')[0];
		r=o.createElement('script');r.async=1;
		r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
		a.appendChild(r);
		})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
EOH;
}

/**
 * Set max srcset image width to 771px, because otherwise WP will display the full resolution version
 */
function set_max_srcset_image_width( $max_width ) {
    $max_width = 771;
    return $max_width;
}
add_filter( 'max_srcset_image_width', 'set_max_srcset_image_width' );

/**
 * remove unecessary WP header stuff
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Remove the Tribe Customizer css <script>
 https://gist.github.com/elimn/50cc4ac8b56cc2809bbc48e7c7e3b461
 */
function tribe_remove_customizer_css(){
	if ( class_exists( 'Tribe__Customizer' ) ) {
		remove_action( 'wp_print_footer_scripts', array( Tribe__Customizer::instance(), 'print_css_template' ), 15 );
	}
}
add_action( 'wp_footer', 'tribe_remove_customizer_css' );

/**
 * remove this theme's style.css, since we're using child-style.css instead
 * see https://github.com/INN/Largo-Sample-Child-Theme/issues/14
 */
remove_action( 'wp_enqueue_scripts', 'largo_enqueue_child_theme_css' );

/**
 * filter search results: remove old events
 */
function cl_pre_get_posts($query) {
	if ( !is_admin() && $query->is_main_query() && $query->is_search ) {
		$meta_query = $query->get('meta_query');
		$additional_query = array(
			'relation' => 'OR',
			array(
				'key' => '_EventStartDate',
				'value' => date("Y-m-d H:i:s"),
				'compare' => '>=',
				'type' => 'DATETIME'
			) ,
			array(
				'key' => '_EventStartDate',
				'compare' => 'NOT EXISTS'
			)
		);
		if ( is_array( $meta_query ) ) {
			$meta_query[] = $additional_query;
		} else {
			$meta_query = $additional_query;
		}

		$query->set('meta_query', $meta_query);
	}
	return $query;
}

add_action( 'pre_get_posts', 'cl_pre_get_posts' );


/**
 * JETPACK RELATED POSTS
 */
 
//remove JP Related Posts from default location so we can move it elsewhere
function jetpackme_remove_rp() {
    if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
        $jprp = Jetpack_RelatedPosts::init();
        $callback = array( $jprp, 'filter_add_target_to_dom' );
        remove_filter( 'the_content', $callback, 40 );
    }
}
add_filter( 'wp', 'jetpackme_remove_rp', 20 );


// limit results to last 3 years
function jetpackme_related_posts_past_3years_only( $date_range ) {
	$date_range = array(
		'from' => strtotime( '-3 years' ),
		'to' => time(),
	);
	return $date_range;
}
add_filter( 'jetpack_relatedposts_filter_date_range', 'jetpackme_related_posts_past_3years_only' );


//eliminate some categories
function jetpackme_filter_exclude_category( $filters ) {
	$filters[] = array( 'not' =>
		array( 'term' => array( 'category.slug' => 'community-wire' ) )
	);
	$filters[] = array( 'not' =>
		array( 'term' => array( 'term' => 'uncategorized' ) )
	);
	return $filters;
}
add_filter( 'jetpack_relatedposts_filter_filters', 'jetpackme_filter_exclude_category' );

//dequeue jetpack css so we can override it
add_filter( 'jetpack_implode_frontend_css', '__return_false', 99 );

/**
 * newsletter subscribe forms
 */

function citylimits_newsletter_form_interstitial() {
	get_template_part( 'partials/newsletter-signup', 'maincolumn' );
}
add_action( 'largo_before_sticky_posts', 'citylimits_newsletter_form_interstitial', 11 );
add_action( 'largo_category_after_primary_featured_post', 'citylimits_newsletter_form_interstitial', 11 );
add_action( 'largo_series_before_stories', 'citylimits_newsletter_form_interstitial', 11 );
add_action( 'largo_archive_before_stories', 'citylimits_newsletter_form_interstitial', 11 );

function citylimits_newsletter_form_footer() {
	get_template_part( 'partials/newsletter-signup', 'footer' );
}
add_action( 'largo_before_footer', 'citylimits_newsletter_form_footer', 11 );

function citylimits_newsletter_form_popover() {
	get_template_part( 'partials/newsletter-signup', 'popover' );
}
if (!$_COOKIE['newsletter_modal_snooze']) {
	add_action( 'wp_footer', 'citylimits_newsletter_form_popover', 11 );
}

add_shortcode('cl-newsletter', function() {
	ob_start();
	get_template_part( 'partials/newsletter-signup', 'maincolumn' );
	return ob_get_clean();
});


/**
 * tell buggy plugins to be quiet in Query Monitor
 */
add_filter( 'qm/collect/php_error_levels', function( array $levels ) {
	$levels['plugin']['taxonomy-converter'] = ( E_ALL & ~E_DEPRECATED );
	$levels['plugin']['news-match-popup-basics'] = ( E_ALL & ~E_NOTICE );
	return $levels;
} );

/**
 * create Options Page for Mailchimp newsletter settings, which are handled by ACF
 */

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Mailchimp Newsletter Settings',
		'menu_title'	=> 'CL Mailchimp',
		'menu_slug' 	=> 'cl-mailchimp-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}
