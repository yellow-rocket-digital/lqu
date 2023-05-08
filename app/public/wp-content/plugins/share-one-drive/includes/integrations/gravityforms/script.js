(function ($) {
    'use strict';

    /* binding to the load field settings event to initialize */
    $(document).on('gform_load_field_settings', function (event, field, form) {
        jQuery('#field_wpcp_shareonedrive').val(field.defaultValue);
        if (field['ShareoneDriveShortcode'] !== undefined && field['ShareoneDriveShortcode'] !== '') {
            jQuery('#field_wpcp_shareonedrive').val(field['ShareoneDriveShortcode']);
        }
    });

    /* Shortcode Generator Popup */
    $('.wpcp-shortcodegenerator.shareonedrive').on('click', function (e) {
        var shortcode = jQuery('#field_wpcp_shareonedrive').val();
        shortcode = shortcode.replace('[shareonedrive ', '').replace('"]', '');
        var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
        tb_show(
            'Build Shortcode for Form',
            ajaxurl +
                '?action=shareonedrive-getpopup&' +
                query +
                '&type=shortcodebuilder&asuploadbox=1&callback=wpcp_sod_gf_add_content&TB_iframe=true&height=600&width=1024'
        );
    });

    /* Callback function to add shortcode to GF field */
    if (typeof window.wpcp_sod_gf_add_content === 'undefined') {
        window.wpcp_sod_gf_add_content = function (data) {
            $('#field_wpcp_shareonedrive').val(data);
            SetFieldProperty('ShareoneDriveShortcode', data);

            tb_remove();
        };
    }
})(jQuery);
