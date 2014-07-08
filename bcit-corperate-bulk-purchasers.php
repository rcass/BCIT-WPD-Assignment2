<?php
/*
Plugin Name: BCIT WPD Coperate Bulk Purchasers
Plugin URI: http://bcit.woo.com
Description: Create custom Bulk Purchaser role and assigns them to specific products in WooCommerce
Version: 1.0
Author: Rose Cass
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once( 'meta.php' );

class BCIT_Custom_Role{

	function __construct(){

		add_action( 'admin_notices', array( $this, 'check_required_plugins') );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue') );


		add_action( 'pre_get_posts', array( $this, 'limit_our_posts' ) ); 
		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook(__FILE__, array( $this, 'activate' ) );
		register_deactivation_hook(__FILE__, array( $this, 'deactivate') );
		register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall' ) );

	} // __construct

	/**
	 * Changes our query so that we don't show our posts where we don't want them
	 *
	 * @since 1.0
	 * @author Rose Cass
	 *
	 * @param array     $query         required         The WP_Query object on the page
	 * @uses is_user_logged_in()                        Returns true if user is logged in
	 */
	public function limit_our_posts( $query ){

		// make sure you write you pre_get_posts stuff to remove any post with a key set (where you saved your user_id)
		// then my filter below should include them back in for a user_id if it matches the current user_id

		if ( is_user_logged_in() ) {
			add_filter( 'posts_where', array( $this, 'custom_where' ) );
		}

	} // limit_our_posts

	/**
	 * Make sure that if a user is logged in we include posts that have their user_id assigned in addition to the
	 * posts that have no key assigned.
	 *
	 * @since 1.0
	 * @author Rose Cass
	 *
	 * @param string        $where          required            existing SQL WHERE clauses in our query
	 * @global $wpdb                                            WordPress database global
	 * @uses get_current_user_id()                              Returns current user_id
	 * @return string       $where                              Our modified WP WHERE SQL clause
	 */
	public function custom_where( $where = '' ){
	
	// make sure to change your meta_key to whatever you saved your user_id as here mine is _restricted_to

		global $wpdb;
		$user_id = get_current_user_id();

		$where .= " OR (( $wpdb->postmeta.meta_key = '_bcit_assign_user' and $wpdb->postmeta.meta_value = $user_id ))";

		return $where;

	} // custom_where
	

	/**
	* Check for WooCommerce and deactivate if we do not find it
	*
	* @since 1.0
	* @author Rose Cass
	*
	* @uses is_plugin_active()			return true if given plugin is active
	* @uses deactive_plugins()			Deactivate give plugin
	* @action admin_notices				Hooked to private WordPress admin notices
	**/

	public function check_required_plugins(){

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) { ?>

			<div id="message" class="error">
				<p>BCIT Corperate Bulk Purchasers expects WooCommerce to be active. This plugin has been deactivated.</p>
			</div>

			<?php
			deactivate_plugins( '/bcit-corperate-bulk-purchasers/bcit-corperate-bulk-purchasers.php' );
		} // if plugin_active WooCommerce

	}// check_required_plugins

	/**
 	 * Registers and enqueues styles
 	 *
 	 * @since 1.0
 	 * @author Rose Cass
 	 * @access public
 	 */
	public function enqueue(){

		// styles plugin
  		wp_enqueue_style( 'bcit_todo_styles', plugins_url( '/bcit-corperate-bulk-purchasers/assets/frontend-styles.css' ), '', '1.0', 'all');

	} // enqueue

	/**
	 * Add Custom role for bulk purchaser
	 *
	 * @since 1.0
	 * @author Rose Cass
	 *
	 * @uses add_role()                 Gets the WP role specified
	 */
    public function add_role(){

    	add_role( 'bulk_purchaser', 'Bulk Purchaser', array( 'read' => true, 'level_0' => true ) );

    } // add_role function

    /**
	 * Remove Custom role for bulk purchaser
	 *
	 * @since 1.0
	 * @author Rose Cass
	 *
	 * @uses remove_role()                 Gets the WP role specified
	 */
    public function remove_role(){

    	remove_role( 'bulk_purchaser', 'Bulk Purchaser', array( 'read' => true, 'level_0' => true ) );

    } // add_role function
 
	/**
	 * Fired when plugin is activated
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function activate( $network_wide ){

		$this->add_role();

	} // activate

	/**
	 * Fired when plugin is deactivated
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function deactivate( $network_wide ){

		$this->remove_role();

	} //deactivate

	/**
	 * Fired when plugin is uninstalled
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function uninstall( $network_wide ){

	} // uninstall


} // BCIT_Custom_Role

new BCIT_Custom_Role();



