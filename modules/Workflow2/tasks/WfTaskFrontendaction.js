jQuery(function() {
    jQuery('#FrontendActionSel').on('change', RenderConfig);

    function RenderConfig() {
        var value = jQuery('#FrontendActionSel').val();

        jQuery('.ConfigContainer').hide();
        jQuery('.ConfigContainer[data-action="' + value + '"]').show();
    }
    RenderConfig();
});