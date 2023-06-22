<?php
/**
 * Widget API: PMPro_Nav_Menu_Widget class
*/

/**
 * Class used to implement the PMPro - Custom Members Menu widget.
 */
class PMPro_Nav_Menu_Widget extends WP_Widget {

	/**
	 * Sets up a new PMPro - Custom Members Menu widget instance.
	 */
	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_nav_menu pmpro_nav_menu_widget', 
			'description' => __( 'Add a membership-conditional menu to your sidebar.', 'pmpro-nav-menus' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'pmpro_nav_menu_widget', __( 'PMPro - Custom Membership Menu', 'pmpro-nav-menus' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current PMPro - Custom Member Menu widget instance.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current PMPro - Custom Member Menu widget instance.
	 */
	public function widget( $args, $instance ) {
		// Get menu
		global $current_user;
		$levels = pmpro_getMembershipLevelsForUser($current_user->ID);
		$level_ids = wp_list_pluck( $levels, 'id' );

		// For logged in non-members...
		if( is_user_logged_in() && empty( $level_ids ) && ! empty( $instance['nav_menu_non_members'] ) ) {
			// Give non-member menu.
			if (  ! empty( $instance['nav_menu_non_members'] ) ) {
				$nav_menu = wp_get_nav_menu_object( $instance['nav_menu_non_members'] );
			}
		}

		// Find menu for membership ID or give membership menu...
		if ( empty( $nav_menu ) && ! empty( $level_ids ) ) {
			// Get levels in priority order.
			$prioritized_levels = apply_filters( 'pmpronm_prioritize_levels', array() );

			// Add levels that are not prioritized.
			$prioritized_levels = array_merge( $prioritized_levels, array_diff( $level_ids, $prioritized_levels ) );

			// Search for menu for prioritized levels.
			foreach ( $prioritized_levels as $prioritized_level_id ) {
				if ( in_array( $prioritized_level_id, $level_ids ) && ! empty( $instance['nav_menu_members_' . $prioritized_level_id] ) ) {
					$nav_menu = wp_get_nav_menu_object( $instance['nav_menu_members_' . $prioritized_level_id] );
					break;
				}
			}

			// Give membership menu if no level menu found.
			if ( empty( $nav_menu ) && ! empty( $instance['nav_menu_members'] ) ) {
				$nav_menu = wp_get_nav_menu_object( $instance['nav_menu_members'] );
			}
		}

		// Give default menu if no membership menu found.
		if ( empty( $nav_menu ) && ! empty( $instance['nav_menu'] ) ) {
			$nav_menu = wp_get_nav_menu_object( $instance['nav_menu'] );
		}

		if ( empty( $nav_menu ) )
			return;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty($instance['title']) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];

		$nav_menu_args = array(
			'fallback_cb' => '',
			'menu'        => $nav_menu
		);

		/**
		 * Filters the arguments for the PMPro - Custom Members Menu widget.
		 *
		 * @since 4.2.0
		 * @since 4.4.0 Added the `$instance` parameter.
		 *
		 * @param array    $nav_menu_args {
		 *     An array of arguments passed to wp_nav_menu() to retrieve a custom menu.
		 *
		 *     @type callable|bool $fallback_cb Callback to fire if the menu doesn't exist. Default empty.
		 *     @type mixed         $menu        Menu ID, slug, or name.
		 * }
		 * @param WP_Term  $nav_menu      Nav menu object for the current menu.
		 * @param array    $args          Display arguments for the current widget.
		 * @param array    $instance      Array of settings for the current widget.
		 */
		wp_nav_menu( apply_filters( 'widget_pmpro_nav_menu_args', $nav_menu_args, $nav_menu, $args, $instance ) );

		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current PMPro - Custom Members Menu widget instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}
		if ( ! empty( $new_instance['nav_menu'] ) ) {
			$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		}
		if ( ! empty( $new_instance['nav_menu_members'] ) ) {
			$instance['nav_menu_members'] = (int) $new_instance['nav_menu_members'];
		}
		if ( ! empty( $new_instance['nav_menu_non_members'] ) ) {
			$instance['nav_menu_non_members'] = (int) $new_instance['nav_menu_non_members'];
		}
		
		//update all nav menus for specific membership levels
		$pmpro_levels = pmpro_getAllLevels(true, true);
		if(!empty($pmpro_levels))
		{
			foreach($pmpro_levels as $level)
			{
				if ( ! empty( $new_instance['nav_menu_members_' . $level->id] ) ) {
					$instance['nav_menu_members_' . $level->id] = (int) $new_instance['nav_menu_members_' . $level->id];
				}
			}
		}
		
		return $instance;
	}

	/**
	 * Outputs the settings form for the PMPro - Custom Members Menu widget.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 * @global WP_Customize_Manager $wp_customize
	 */
	public function form( $instance ) {
		global $wp_customize;
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$nav_menu_members = isset( $instance['nav_menu_members'] ) ? $instance['nav_menu_members'] : '';
		$nav_menu_non_members = isset( $instance['nav_menu_non_members'] ) ? $instance['nav_menu_non_members'] : '';

	


		// Get menus
		$menus = wp_get_nav_menus();


		// If no menus exists, direct the user to go and create some.
		?>
		<p class="nav-menu-widget-no-menus-message" <?php if ( ! empty( $menus ) ) { echo ' style="display:none" '; } ?>>
			<?php
			if ( $wp_customize instanceof WP_Customize_Manager ) {
				$url = 'javascript: wp.customize.panel( "nav_menus" ).focus();';
			} else {
				$url = admin_url( 'nav-menus.php' );
			}
			?>
			<?php echo sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.', 'pmpro-nav-menus' ), esc_attr( $url ) ); ?>
		</p>
		<div class="nav-menu-widget-form-controls" <?php if ( empty( $menus ) ) { echo ' style="display:none" '; } ?>>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'nav_menu' ); ?>"><?php _e( 'Default Menu:' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_menu' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'nav_menu_members' ); ?>"><?php _e( 'Members Menu:' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_menu_members' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu_members' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu_members, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'nav_menu_non_members' ); ?>"><?php esc_html_e( 'Logged-in Non-member Menu:', 'pmpro-nav-menus' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_menu_non_members' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu_non_members' ); ?>">
					<option value="0"><?php echo '&mdash; ' . esc_html( 'Select' ) . ' &mdash;'; ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu_non_members, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<?php

				$pmpro_levels = pmpro_getAllLevels(true, true);
				if(!empty($pmpro_levels))
				{
					//check if we have settings for at least one level
					$has_level_settings = false;
					foreach($pmpro_levels as $level) {
						if(!empty($instance['nav_menu_members_' . $level->id])) {
							$has_level_settings = true;
							break;
						}
					}


				?>
				<p class="pmpro_nav_menu_level_settings_trigger" style="text-align: center; <?php if($has_level_settings) {?>display: none;<?php } ?>"><a href="#show" style="cursor:pointer;"><?php esc_html_e( 'Click here to set menus for specific levels.', 'pmpro-nav-menus' ); ?></a></p>
				<div class="pmpro_nav_menu_level_settings" <?php if(!$has_level_settings) {?>style="display: none;"<?php } ?>>
				<?php
					foreach($pmpro_levels as $level)
					{
						if(isset($instance['nav_menu_members_' . $level->id]))
							$selected_menu = $instance['nav_menu_members_' . $level->id];
						else
							$selected_menu = false;
						?>
						<p>
							<label for="<?php echo $this->get_field_id( 'nav_menu_members_' . $level->id); ?>"><?php echo sprintf( esc_html( '%s Menu:', 'pmpro-nav-menus' ), $level->name ); ?></label>
							<select id="<?php echo $this->get_field_id( 'nav_menu_members_' . $level->id ); ?>" name="<?php echo $this->get_field_name( 'nav_menu_members_' . $level->id ); ?>">
								<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
								<?php foreach ( $menus as $menu ) : ?>
									<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $selected_menu, $menu->term_id ); ?>>
										<?php echo esc_html( $menu->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</p>	
						<?php
					}
				?>
				</div>
				<script>
					jQuery('.pmpro_nav_menu_level_settings_trigger a').click(function() {
						jQuery(this).closest('.pmpro_nav_menu_level_settings_trigger').hide();
						jQuery(this).closest('.pmpro_nav_menu_level_settings_trigger').next('.pmpro_nav_menu_level_settings').show();
					});
				</script>
				<?php
				}
			?>
			<?php if ( $wp_customize instanceof WP_Customize_Manager ) : ?>
				<p class="edit-selected-nav-menu" style="<?php if ( ! $nav_menu ) { echo 'display: none;'; } ?>">
					<button type="button" class="button"><?php esc_html_e( 'Edit Menu', 'pmpro-nav-menus' ); ?></button>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
