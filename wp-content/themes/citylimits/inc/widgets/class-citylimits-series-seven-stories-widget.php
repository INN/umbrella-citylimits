<?php
/**
 * City Limits "Seven Series Posts" widget, and associated function
 */

/**
 * Register the widget
 */
add_action( 'widgets_init', function() {
	register_widget( 'Citylimits_Series_Seven_Stories_Widget' );
});

/**
 * The City Limits widget class for seven stories from the present series
 *
 * Based on the code-cleanup version of Largo Recent Posts from https://github.com/INN/umbrella-borderzine/pull/67/files
 *
 */
class Citylimits_Series_Seven_Stories_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname' => 'citylimits-seven-series-posts',
			'description' => __( 'Seven posts from the series on the present page', 'citylimits' ),
		);
		parent::__construct(
			'citylimits-series-posts', // Base ID
			__( 'City Limits Seven Series Posts', 'citylimits' ), // Name
			$widget_ops // Args
		);

	}

	/**
	 * Outputs the content of the recent posts widget.
	 *
	 * @param array $args widget arguments.
	 * @param array $instance saved values from databse.
	 * @global $post
	 * @global $shown_ids An array of post IDs already on the page, to avoid duplicating posts
	 * @global $wp_query Used to get posts on the page not in $shown_ids, to avoid duplicating posts
	 */
	public function widget( $args, $instance ) {

		global $post,
			$wp_query, // grab this to copy posts in the main column
			$shown_ids; // an array of post IDs already on a page so we can avoid duplicating posts;

		// Preserve global $post
		$preserve = $post;


		// Add the link to the title.
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$excerpt = isset( $instance['excerpt_display'] ) ? $instance['excerpt_display'] : 'num_sentences';

		if (
			isset( $wp_query->query_vars['term'] )
			&& isset( $wp_query->query_vars['taxonomy'] )
			&& 'series' == $wp_query->query_vars['taxonomy']
		) {
			$series = $wp_query->query_vars['term'];
		} else {
			printf(
				'<!-- %1$s %2$s -->',
				__( 'A City Limits Seven Series Posts widget is being used where it should not be used', 'citylimits'),
				var_export( $args['name'], true )
			);
		}

		$query_args = array (
			'post__not_in'   => get_option( 'sticky_posts' ),
			'posts_per_page' => 7,
			'post_status'    => 'publish',
			'tax_query'      => array(
				array(
					'taxonomy' => 'series',
					'field'    => 'slug',
					'terms'    => $series,
				),
			)
		);

		if ( isset( $instance['avoid_duplicates'] ) && 1 === $instance['avoid_duplicates'] ) {
			$query_args['post__not_in'] = $shown_ids;
		}

		/*
		 * here begins the widget output
		 */

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . wp_kses_post( $title ). $args['after_title'];
		}

		$posts = get_posts( $query_args );

		if ( count( $posts ) > 0 ) {

			$output = '<ul>';

			global $post;
			$preserve = $post;
			$counter = 1;

			foreach ( $posts as $p ) {
				setup_postdata( $p );
				$post = $p;
				$shown_ids[] = get_the_ID();

				// wrap the items in li's.
				$output .= sprintf(
					'<li class="%1$s" >',
					implode( ' ', get_post_class( '', get_the_ID() ) )
				);

				$context = array(
					'instance' => $instance,
					'thumb' => '',
					'excerpt' => $excerpt,
					'podcast' => false,
				);

				if ( $counter === 1 ) {
					$context['instance']['image_align'] = 'none';
					$context['instance']['thumb'] = 'large';
					$context['thumb'] = 'large';
				} else {
					$context['excerpt'] = false;
				}

				ob_start();
				largo_render_template( 'partials/widget', 'content', $context );
				$output .= ob_get_clean();
				$counter++;

				// close the item
				$output .= '</li>';


				// cleanup
				wp_reset_postdata();

			} // end foreach

			$post = $preserve;


			// close the ul
			$output .= '</ul>';

			// print all of the items
			echo $output;

		} else {
			printf(
				'<p class="error"><strong>%1$s</strong></p>',
				sprintf(
					// translators: %s is the word this site uses for "posts", like "articles" or "stories". It's a plural noun.
					esc_html__( 'You don\'t have any recent %s', 'largo' ),
					of_get_option( 'posts_term_plural', 'Posts' )
				)
			);
		} // end more featured posts


		if ( ! empty( $instance['linkurl'] ) && ! empty( $instance['linktext'] ) ) {
			echo '<a class="morelink btn" href="' . esc_url( $instance['linkurl'] ) . '">' . esc_html( $instance['linktext'] ) . '</a>';
		}

		// close the widget
		echo wp_kses_post( $args['after_widget'] );

		// Restore global $post
		wp_reset_postdata();
		$post = $preserve;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['avoid_duplicates'] = ! empty( $new_instance['avoid_duplicates'] ) ? 1 : 0;
		$instance['excerpt_display'] = sanitize_key( $new_instance['excerpt_display'] );
		$instance['show_byline'] = ! empty( $new_instance['show_byline'] );
		$instance['hide_byline_date'] = true;
		$instance['linktext'] = sanitize_text_field( $new_instance['linktext'] );
		$instance['linkurl'] = esc_url_raw( $new_instance['linkurl'] );
		return $instance;
	}

	public function form( $instance ) {
		$defaults = array(
			'title' => sprintf(
				// translators: %s is the word this site uses for "posts", like "articles" or "stories". It's a plural noun.
				__( 'Recent %1$s' , 'largo' ),
				of_get_option( 'posts_term_plural', 'Posts' )
			),
			'avoid_duplicates' => '',
			'excerpt_display' => 'num_sentences',
			'show_byline' => '',
			'linktext' => '',
			'linkurl' => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$duplicates = $instance['avoid_duplicates'] ? 'checked="checked"' : '';
		$showbyline = $instance['show_byline'] ? 'checked="checked"' : '';
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'largo' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:90%;" type="text" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'excerpt_display' ) ); ?>"><?php esc_html_e( 'First post excerpt display option:', 'citylimits' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'excerpt_display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'excerpt_display' ) ); ?>" class="widefat" style="width:90%;">
				<option <?php selected( $instance['excerpt_display'], 'custom_excerpt' ); ?> value="custom_excerpt"><?php esc_html_e( 'Use Custom Post Excerpt', 'largo' ); ?></option>
				<option <?php selected( $instance['excerpt_display'], 'none' ); ?> value="none"><?php esc_html_e( 'None', 'largo' ); ?></option>
			</select>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php echo $duplicates; ?> id="<?php echo esc_attr( $this->get_field_id( 'avoid_duplicates' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'avoid_duplicates' ) ); ?>" /> <label for="<?php echo esc_attr( $this->get_field_id( 'avoid_duplicates' ) ); ?>"><?php esc_html_e( 'Avoid showing posts here shown earlier on the same page?', 'citylimits' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php echo $showbyline; ?> id="<?php echo esc_attr( $this->get_field_id( 'show_byline' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_byline' ) ); ?>" /> <label for="<?php echo esc_attr( $this->get_field_id( 'show_byline' ) ); ?>"><?php esc_html_e( 'Show byline on posts?', 'citylimits' ); ?></label>
		</p>

		<p>
			<strong><?php esc_html_e( 'More Link', 'largo' ); ?></strong>
			<br />
			<small><?php esc_html_e( 'If you would like to add a more link at the bottom of the widget, add the link text and url here.', 'largo' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linktext' ) ); ?>"><?php esc_html_e( 'Link text:', 'largo' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linktext' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linktext' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['linktext'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>"><?php esc_html_e( 'URL:', 'largo' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linkurl' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['linkurl'] ); ?>" />
		</p>

		<?php
	}
}
