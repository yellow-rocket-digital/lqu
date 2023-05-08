(function ($) {
    FLBuilder._registerModuleHelper('wpcp_shareonedrive_module', {
        /**
         * The 'rules' property is where you setup
         * validation rules that are passed to the jQuery
         * validate plugin (http://jqueryvalidation.org).
         *
         * @property rules
         * @type object
         */
        rules: {
            raw_shortcode: {
                required: true
            }
        },

        /**
         * The 'init' method is called by the builder when
         * the settings form is opened.
         *
         * @method init
         */
        init: function () {
            var self = this;

            $('#fl-raw_shortcode-select').on('click', function () {
                window.addEventListener('message', self.callback_handler);
                self.openShortcodeBuilder();
            });
        },

        callback_handler: function (event) {
            if (event.origin !== window.parent.location.origin) {
                return;
            }

            if (
                typeof event.data !== 'object' ||
                event.data === null ||
                typeof event.data.action === 'undefined' ||
                typeof event.data.shortcode === 'undefined'
            ) {
                return;
            }

            if (event.data.action !== 'wpcp-shortcode') {
                return;
            }

            if (event.data.slug !== 'shareonedrive') {
                return;
            }

            $('#fl-raw_shortcode-textarea').val(event.data.shortcode).trigger('input');
            window.modal_action.close();
            $('#shareonedrive-modal-action').remove();

            window.removeEventListener('message', self.callback_handler);
        },

        openShortcodeBuilder: function () {
            if ($('#shareonedrive-modal-action').length > 0) {
                window.modal_action.close();
                $('#shareonedrive-modal-action').remove();
            }

            /* Build the  Dialog */
            var modalbuttons = '';
            var modalheader = $(
                '<a tabindex="0" class="close-button" title="" onclick="modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a></div>'
            );
            var modalbody = $('<div class="shareonedrive-modal-body" tabindex="0" style="display:none"></div>');
            var modalfooter = $(
                '<div class="shareonedrive-modal-footer" style="display:none"><div class="shareonedrive-modal-buttons">' +
                    '' +
                    '</div></div>'
            );
            var modaldialog = $(
                '<div id="shareonedrive-modal-action" class="ShareoneDrive shareonedrive-modal shareonedrive-modal80 light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
            );

            $('body').append(modaldialog);

            var shortcode = $('#fl-raw_shortcode-textarea').val();
            var shortcode_attr = shortcode.replace('</p>', '').replace('<p>', '').replace('[shareonedrive ', '').replace('"]', '');
            var query = encodeURIComponent(shortcode_attr).split('%3D%22').join('=').split('%22%20').join('&');

            var $iframe_template = $(
                "<iframe src='" +
                    window.ajaxurl +
                    '?action=shareonedrive-getpopup&type=shortcodebuilder&' +
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
                escapeClose: true,
                afterClose() {
                    window.removeEventListener('message', self.callback_handler);
                }
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
    });
})(jQuery);
