<?php
/**
 * Functions related to term metadata
 *
 * This file contains the "Status" and "Location" (latitude and longitude) term meta for the 'neighborhoods' taxonomy
 * It does not do it in a Largo way, but in a post-Wordpress-4.4 Wordpress way.
 */

add_action( 'neighborhoods_edit_form_fields', 'cl_status_edit_status_form', 10, 2 );

/**
 * The form for the "status" term meta of neightborhoods
 */
function cl_status_edit_status_form( $tag, $taxonomy ) {
	$statuses = cl_status_get_statuses();
	$current_status = cl_status_get_status( $tag, $taxonomy );

	?>
		<tr class="form-field term-group">
			<th scope="row">
				<label for="neighborhood-status"><?php _e('Neighborhood Zoning Status', 'citylimits'); ?></label>
			</th>
			<td>

				<select class="postform" id="neighborhood-status" name="neighborhood-status">
					<option value=''><?php _e("No status set", 'citylimits'); ?></option>
					<?php
						foreach ( $statuses as $id => $values ) {
							printf(
								'<option value="%1$s" ' . selected( $current_status, $id ) . '>%2$s</option>',
								$id,
								__( $values['color'], 'citylimits' )
							);
						}
					?>
				</select>
			</td>
		</tr>

	<?php
}

/**
 * Get the status for a neighborhood
 */
function cl_status_get_status( $term, $taxonomy ) {
	return get_term_meta( $term->term_id, 'neighborhood-status', true );
}

/**
 * Return array of valid statuses
 * @return array of id => array ( 'color' => color, 'label' => label text )
 */
function cl_status_get_statuses() {
	$temporary_statuses = array(
		'red' => array(
			'color' => 'red',
			'label' => 'Proposal Defeated or Withdrawn'
		),
		'yellow' => array(
			'color' => 'yellow',
			'label' => 'Proposal in Approval Process'
		),
		'green' => array(
			'color' => 'green',
			'label' => 'Proposal Approved'
		),
		'blue' => array(
			'color' => 'blue',
			'label' => 'Proposal Anticipated'
		)
	);

	return $temporary_statuses;
}

function citylimits_update_project_status( $term_id, $tt_id ) {
	if ( isset( $_POST['neighborhood-status'] ) ) {
		if ( '' !== $_POST['neighborhood-status'] ) {
			$group = sanitize_title( $_POST['neighborhood-status'] );
			update_term_meta( $term_id, 'neighborhood-status', $group );
		} else {
			delete_term_meta( $term_id, 'neighborhood-status' );
		}
	}
}
add_action( 'edited_neighborhoods', 'citylimits_update_project_status', 10, 2 );



add_action( 'neighborhoods_edit_form_fields', 'cl_latlon_edit_latlon_form', 10, 2 );

/**
 * The form for the "latlon" term meta of neightborhoods
 */
function cl_latlon_edit_latlon_form( $tag, $taxonomy ) {
	$current_status = cl_latlon_get_latlon( $tag, $taxonomy );

	?>
		<tr class="form-field term-group">
			<th scope="row">
				<label for="neighborhood-latlon"><?php _e('Neighborhood Location (latitute, longitude)', 'citylimits'); ?></label>
			</th>
			<td>


				<?php if ($current_status != '') {
					print '<input type="text" class="postform" id="neighborhood-latlon" name="neighborhood-latlon" value="' . $current_status . '"></input>';
				} else {
					print '<input type="text" class="postform" id="neighborhood-latlon" name="neighborhood-latlon"></input>';
				} ?>


			</td>
		</tr>

	<?php
}

/**
 * Get the latlon for a neighborhood
 */
function cl_latlon_get_latlon( $term, $taxonomy ) {
	return get_term_meta( $term->term_id, 'neighborhood-latlon', true );
}

function citylimits_update_project_latlon( $term_id, $tt_id ) {
	if ( isset( $_POST['neighborhood-latlon'] ) ) {
		if ( '' !== $_POST['neighborhood-latlon'] ) {
			$location = $_POST['neighborhood-latlon'];
			update_term_meta( $term_id, 'neighborhood-latlon', $location );
		} else {
			delete_term_meta( $term_id, 'neighborhood-latlon' );
		}
	}
}
add_action( 'edited_neighborhoods', 'citylimits_update_project_latlon', 10, 2 );

/**
 * Overriding Largo function to prevent insertion of empty _term_meta posts
 * see https://github.com/INN/largo/issues/1014
 * Get the proxy post for a term
 *
 * @param string $taxnomy The taxonomy of the term for which you want to retrieve a term meta post
 * @param int $term_id The ID of the term
 * @return int $post_id The ID of the term meta post
 */
if ( ! function_exists ( 'largo_get_term_meta_post' ) ) {
	function largo_get_term_meta_post( $taxonomy, $term_id ) {
		$query = new WP_Query( array(
			'post_type'      => '_term_meta',
			'posts_per_page' => 1,
			'post_status' => 'any',
			'tax_query'      => array(
				array(
					'taxonomy'         => $taxonomy,
					'field'            => 'id',
					'terms'            => $term_id,
					'include_children' => false
				)
			)
		));

		if ( $query->found_posts ) {
			return $query->posts[0]->ID;
		} else {
			$tax_input = array();
			$post_id = wp_insert_post( array( 'post_type' => '_term_meta', 'post_title' => "{$taxonomy}:${term_id}" ) );
			if ($taxonomy && $term_id) {
				wp_set_post_terms( $post_id, array( (int) $term_id ), $taxonomy );
				return $post_id;
			}
			return null;
		}
	}
}

