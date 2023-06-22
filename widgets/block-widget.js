(function ($) {
    $(document).on('widget-added', function ($event, $control) {
        $control.find('.pmpro_nav_menu_level_settings_trigger a').on('click', function () {
            $control.find('.pmpro_nav_menu_level_settings_trigger').hide();
            $control.find('.pmpro_nav_menu_level_settings_trigger').next('.pmpro_nav_menu_level_settings').show();
        });
    });
})(jQuery);