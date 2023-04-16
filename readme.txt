=== Paid Memberships Pro: Nav Menus Add On ===
Contributors: strangerstudios
Tags: pmpro, paid memberships pro, members, memberships, navigation, menu, menus
Requires at least: 3.5
Tested up to: 6.2
Stable tag: .3.4

Creates member navigation menus and swaps your theme's navigation based on a user's Membership Level

== Description ==

Extend your theme to display unique navigation menus based on a user's membership level. 

This plugin duplicates all wp_nav_menus defined by your theme and creates a members version. Customize menus even further by optionally creating a level-specific navigation menu. This can be set on the Edit Membership Level screen under the "Navigation Menu" section.

If you do not set a Menu for a member-specific Theme Location, the menu will fall back to the default as defined by your theme.

== Installation ==

1. Upload the `pmpro-nav-menus` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally create level-specific navigation menus on the `Edit Membership Level` admin.
1. Create new navigation menu(s) for your members and assign them to the appropriate Theme Location.

== Changelog == 
= .3.4 - 2020-03-17 =
* BUG FIX: Fixed issue where logged-in non-members would not see correct menu
* ENHANCEMENT: Added pmpronm_prioritize_levels filter for MMPU compatibility

= .3.3 =
* BUG FIX: Now shows correct menu for non-member users.

= .3.2 =
* ENHANCEMENT: Added a widget setting for logged-in non-members.
* ENHANCEMENT: Added a menu area for logged-in non-members.

= .3.1 =
* ENHANCEMENT: Added a PMPro Custom Menu Widget. The widget filtering we were doing before only worked on menus that were also attached to menu locations. The PMPro Custom Menu Widget will work for menus only used in widgets. The widget has a property to choose who the menu is shown to.

= .3 =
* ENHANCEMENT: Now also filtering the Custom Menu Widget so member menus are shown.

= .2 =
* BUG/ENHANCEMENT: Changed priority when nav menus are created to make sure theme menus are already in place. (Thanks, Joe Anderson - meta4creations on GitHub)
* NOTE: Added pmpronm_ prefix to all function names.

= .1.2 =
* Fixed bug where a fatal error would be thrown if Paid Memberships Pro was not activated. (PMPro still needs to be active for this plugin to work.)

= .1.1 =
* Fixed bug where hidden levels weren't having menu locations created for them. (Thanks, contemplate)

= .1 =
* First version.