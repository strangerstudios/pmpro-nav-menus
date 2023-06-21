<?php
/*
Plugin Name: Paid Memberships Pro - Nav Menus Add On
Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-nav-menus/
Description: Creates member navigation menus and swaps your theme's navigation based on a user's Membership Level
Version: .3.4
Author: Stranger Studios
Author URI: https://www.paidmembershipspro.com
*/

function pmpro_nav_menus_widgets_init() {
	// Make sure PMPro is activated.
	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}
	/** WP_Nav_Menu_Widget class */
	$path = dirname(__FILE__);
	require_once($path . "/widgets/class-pmpro-nav-menu-widget.php");
	register_widget('PMPro_Nav_Menu_Widget');
}
add_action( 'widgets_init', 'pmpro_nav_menus_widgets_init' );

/*
	Add checkbox to create custom navigation menu location for this level.
*/
//show the checkbox on the edit level page
function pmpronm_pmpro_membership_level_after_other_settings()
{	
	$level_id = intval($_REQUEST['edit']);
	if($level_id > 0)
		$pmpro_nav_menu = get_option('pmpro_nav_menu_hidden_level_' . $level_id);	
	else
		$pmpro_nav_menu = false;
?>
<h3 class="topborder">Navigation Menu</h3>
<table>
<tbody class="form-table">
	<tr>
		<th scope="row" valign="top"><label for="pmpro_nav_menu"><?php _e('Custom Menu:', 'pmpro');?></label></th>
		<td>
			<input type="checkbox" id="pmpro_nav_menu" name="pmpro_nav_menu" value="1" <?php checked($pmpro_nav_menu, 1);?> />
			<label for="pmpro_nav_menu"><?php _e('Check this if you want to create unique navigation menus for this level.', 'pmpro');?></label>
		</td>
	</tr>
</tbody>
</table>
<?php
}
add_action('pmpro_membership_level_after_other_settings', 'pmpronm_pmpro_membership_level_after_other_settings');

//save navigation menu setting when the level is saved/added
function pmpronm_pmpro_save_membership_level($level_id)
{
	if(isset($_REQUEST['pmpro_nav_menu']))
		$pmpro_nav_menu = intval($_REQUEST['pmpro_nav_menu']);
	else
		$pmpro_nav_menu = 0;
	update_option('pmpro_nav_menu_hidden_level_' . $level_id, $pmpro_nav_menu);
}
add_action("pmpro_save_membership_level", "pmpronm_pmpro_save_membership_level");

//register additional navigation menus
function pmpronm_register_my_members_menu() {
	//make sure PMPro is activated
	if(!function_exists('pmpro_getAllLevels'))
		return;

	$my_theme = wp_get_theme();
	$menus = get_registered_nav_menus();
	foreach ($menus as $location => $description)
	{
		register_nav_menu( 'members-' . $location, __( $description . ' - Members', $my_theme->get( 'Template') ) );

		register_nav_menu( 'pmpro-non-members-' . $location, __( $description . ' - Logged-in Non-members', $my_theme->get( 'Template') ) );

		$levels = pmpro_getAllLevels(true, true);
		foreach($levels as $level)
		{
			$level_nav_menu = get_option('pmpro_nav_menu_hidden_level_' . $level->id, false);
			if(!empty($level_nav_menu))
			{
				register_nav_menu( 'members-' . $level->id . '-' . $location, __( $description . ' - ' . $level->name . ' Members', 'pmpro' ) );
			}
		}
	}
}
add_action( 'init', 'pmpronm_register_my_members_menu', 15 );

function pmpronm_modify_nav_menu_args( $args )
{
	//make sure PMPro is active
	if(!function_exists('pmpro_hasMembershipLevel'))
		return $args;
	
	if( !is_user_logged_in() ){
		return $args;
	}
	
	
	//get current user's level ids
	global $current_user;
	$levels = pmpro_getMembershipLevelsForUser($current_user->ID);
	$level_ids = wp_list_pluck( $levels, 'id' );
	$found_menu = false;

	// For logged in non-members...
	if( is_user_logged_in() && empty( $level_ids ) ) {
		// Give non-member menu.
		if ( has_nav_menu( "pmpro-non-members-" . $args['theme_location'] ) ) {
			$args['theme_location'] = "pmpro-non-members-" . $args['theme_location'];
			$found_menu = true;
		}
	}

	// Find menu for membership ID, or give membership menu...
	if ( ! $found_menu && ! empty( $level_ids ) ) {
		// Get levels in priority order.
		$prioritized_levels = apply_filters( 'pmpronm_prioritize_levels', array() );

		// Add levels that are not prioritized.
		$prioritized_levels = array_merge( $prioritized_levels, array_diff( $level_ids, $prioritized_levels ) );
		foreach ( $prioritized_levels as $prioritized_level_id ) {
			if ( in_array( $prioritized_level_id, $level_ids ) && has_nav_menu("members-" . $prioritized_level_id . "-" . $args['theme_location']) ) {
				$args['theme_location'] = "members-" . $prioritized_level_id . "-" . $args['theme_location'];
				$found_menu = true;
				break;
			}
		}

		if ( ! $found_menu && has_nav_menu( 'members-' . $args['theme_location'] ) ) {
			$args['theme_location'] = 'members-' . $args['theme_location'];
		}
	}

	return $args;
}
add_filter( 'wp_nav_menu_args', 'pmpronm_modify_nav_menu_args' );

/*
Function to add links to the plugin row meta
*/
function pmpronm_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-nav-menus.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('https://www.paidmembershipspro.com/add-ons/pmpro-nav-menus/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmpronm_plugin_row_meta', 10, 2);
