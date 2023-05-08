(function ($) {
    'use strict';

    $(window).on('elementor/frontend/init', function () {
        elementor.channels.editor.on('wpcp:editor:edit_shareonedrive_shortcode', openShortcodeBuilder);
        elementorFrontend.hooks.addAction('frontend/element_ready/wpcp-shareonedrive.default', function () {
            $('.ShareoneDrive').parent().trigger('inview');
        });
    });

    function openShortcodeBuilder(view) {
        window.wpcp_sod_elementor_add_content = function (value) {
            view._parent.model.setSetting('shortcode', value);
            window.parent.jQuery('.elementor-control-shortcode textarea').trigger('input');
            window.modal_action.close();
            $('#shareonedrive-modal-action').remove();
        };

        if ($('#shareonedrive-modal-action').length > 0) {
            if (typeof window.modal_action !== 'undefined') {
                window.modal_action.close();
            }
            $('#shareonedrive-modal-action').remove();
        }

        /* Build the  Dialog */
        var modalbuttons = '';
        var modalheader = $(
            '<a tabindex="0" class="close-button" title="" onclick="modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a></div>'
        );
        var modalbody = $('<div class="shareonedrive-modal-body" tabindex="0" style="display:none"></div>');
        var modalfooter = $(
            '<div class="shareonedrive-modal-footer" style="display:none"><div class="shareonedrive-modal-buttons">' + '' + '</div></div>'
        );
        var modaldialog = $(
            '<div id="shareonedrive-modal-action" class="ShareoneDrive shareonedrive-modal shareonedrive-modal80 light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
        );

        $('body').append(modaldialog);

        var shortcode = view._parent.model.getSetting('shortcode', 'true');
        var shortcode_attr = shortcode.replace('</p>', '').replace('<p>', '').replace('[shareonedrive ', '').replace('"]', '');
        var query = encodeURIComponent(shortcode_attr).split('%3D%22').join('=').split('%22%20').join('&');

        var $iframe_template = $(
            "<iframe src='" +
                ShareoneDrive_vars.ajax_url +
                '?action=shareonedrive-getpopup&type=shortcodebuilder&callback=wpcp_sod_elementor_add_content&' +
                query +
                "' width='100%' height='500' tabindex='-1' frameborder='0'></iframe>"
        );
        var $iframe = $iframe_template.appendTo(modalbody);

        $('#shareonedrive-modal-action .modal-content').append(modalheader, modalbody, modalfooter);

        $iframe.on('load', function () {
            $('.shareonedrive-modal-body').fadeIn();
            $('.shareonedrive-modal-footer').fadeIn();
            $('.modal-content .loading:first').fadeOut();
        });

        /* Open the Dialog */
        var modal_action = new RModal(document.getElementById('shareonedrive-modal-action'), {
            bodyClass: 'rmodal-open',
            dialogOpenClass: 'animated slideInDown',
            dialogCloseClass: 'animated slideOutUp',
            escapeClose: true
        });
        document.addEventListener(
            'keydown',
            function (ev) {
                modal_action.keydown(ev);
            },
            false
        );
        modal_action.open();
        window.modal_action = modal_action;
    }
})(jQuery);
