<?php

/**
 * Create Metabox to pick user with the bulk purchaser role and assign to product.
 *
 * @since 1.0
 * @author Rose
 */

class BCIT_Assign_Purchase_Meta{

	function __construct(){

		add_action( 'load-post.php', array( $this, 'metaboxes_setup' ) );
		add_action( 'load-post-new.php', array( $this, 'metaboxes_setup' ) );


		 // Get constant
	    add_action( 'load-post', array( $this, 'get_permitted_users' ) );

	    // Another attempt at pre_get_posts
	    // add_action( 'pre_get_posts', array( $this, 'custom_user_posts_query_modifier' ) );

	   
	} // __construct

	// /**
	//	* Filter out Posts with meta value set
	//  *
	//  * @uses    add_meta_box
	//  *
	//  * @since   1.0
	//  * @author  Rose Cass
	//  */
	//   public function custom_user_posts_query_modifier( $query ){

	//     // if logged in
	//     // has assigned products
	//     if( is_user_logged_in() && $query->is_main_query()  ) {

	//       $meta_query = $query->get('meta_query');

	//       //Add our meta query to the original meta queries
	//       // Default 'compare' operator is '='
	//       $meta_query = array(

	//                       array(
	//                           'key'       => '_bcit_assign_user',
	//                           'value'     => array(0),
	//                           //'compare'   => 'NOT EXISTS',
	//                         ),

	//                       );
	//       $query->set( 'meta_query', $meta_query );

	//       return $query;
	//     }

	//   } // custom_user_posts_query_modifier()

	  // public function get_permitted_users(){

	  //   $permitted_users = get_users( array( 'role' => 'bulk_purchaser' ) );
	  //   return $permitted_users;
	  // } //get_permitted_users()
	
	/**
	 * Adds our actions so that we can start the build out of the post metaboxes
	 *
	 * @since   1.0
	 * @author  Rose Cass
	 */
	public function metaboxes_setup(){

		// adds the action which actually adds the meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_post_metaboxes' ) );

		// all the saving actions go here
		add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 2 );

	} // metaboxes_setup

	/**
	 * Adds the actual metabox
	 *
	 * @uses    add_meta_box
	 *
	 * @since   1.0
	 * @author  Rose Cass
	 */
	public function add_post_metaboxes(){

		add_meta_box(
			'bcit-assign-user',         		 // $id - HTML 'id' attribute of the edit screen section
			'Corperate Bulk Purchaser',          // $title - Title that will show at the top of the metabox
			array( $this, 'display_metaboxes' ), // $callback - The function that will display the metaboxes
			'product',                           // $posttype - The registered name of the post type you want to show on
			'side',                              // $context - Where it shows on the page. Possibilities are 'normal', 'advanced', 'side'
			'high'                               // $priority - How high should this display?
			//'$callback_args'                   // any extra params that the callback should get. It will already get the $post_object
		);

	} // add_post_metaboxes

	/**
	 * Display users with Bulk Purchaser role to select in metabox
	 *
	 * @param   object  $object     req     The whole post object for the metabox
	 * @param   array   $box        rex     Array of the box arguements
	 *
	 * @uses    get_post_meta
	 * @uses    wp_nonce_field
	 * @uses 	selected( $selected, $current, $echo)
	 *
	 * @since   1.0
	 * @author  Rose Cass
	 */
	public function display_metaboxes( $post_object, $box ){

		wp_nonce_field( basename( __FILE__ ), 'bcit_meta_nonce'.$post_object->ID );

		$value = get_post_meta( $post_object->ID, '_bcit_assign_user', true );

	?>

		<p>
			<label for="meta-select" class="prfx-row-title">Choose a Bulk Purchaser</label><br />
			<select name="bcit_assign_user" id="bcit_assign_user" >
				<option value="">None assigned</option>

			<?php 
				$user_query = new WP_User_Query( array( 'role' => 'bulk_purchaser') );

				if( ! empty( $user_query->results ) ){

				foreach ( $user_query->results as $user ) {
					echo '<option value="'. absint($user->ID) .'"'. selected( $value, absint($user->ID) ) . '>' . esc_html($user->display_name) . '</option>';
				}

			} else {
				echo 'No Bulk Purchasers found.';
			}
			?>
        	</select>
		</p>

	<?php
	} // display_metaboxes


	/**
	 * Saves the metaboxes
	 *
	 * @param   int     $post_id    req     The ID of the post we're saving8
	 * @param   object  $post       req     The whole post object
	 * @return  mixed
	 *
	 * @uses    wp_verify_nonce
	 * @uses    delete_post_meta
	 * @uses    update_post_meta
	 *
	 * @since   1.0
	 * @author  Rose Cass
	 */
	public function save_post_meta( $post_id, $post ){

		// check the nonce before we do any processing
		if ( ! isset ( $_POST[ 'bcit_meta_nonce'.$post_id ] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST[ 'bcit_meta_nonce'.$post_id ], basename( __FILE__ ) ) ){
			return $post_id;
		}

		if (  empty( $_POST['bcit_assign_user'] ) ) {
			delete_post_meta( $post_id, '_bcit_assign_user' );
		} else {
			$value = strip_tags( $_POST['bcit_assign_user'] );
			update_post_meta( $post_id, '_bcit_assign_user', absint( $value ) );
		}
	}

} // BCIT_Assign_Purchase_Meta

new BCIT_Assign_Purchase_Meta();