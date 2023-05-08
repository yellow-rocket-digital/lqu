'use strict';

(function () {
    var sod_toolbarActive = false;

    // CallBack function to add content to Classic MCE editor //
    window.wpcp_add_content_to_mce = function (content) {
        tinymce.activeEditor.execCommand('mceInsertContent', false, content);
        tinymce.activeEditor.windowManager.close();
        tinymce.activeEditor.focus();
    };

    tinymce.PluginManager.add('shareonedrive', function (ed, url) {
        var t = this;
        t.url = url;

        ed.addCommand('mceShareoneDrive', function (query) {
            ed.windowManager.open(
                {
                    file: ajaxurl + '?action=shareonedrive-getpopup&type=shortcodebuilder&' + query + '&callback=wpcp_add_content_to_mce',
                    width: 1024,
                    height: 600,
                    inline: 1
                },
                {
                    plugin_url: url
                }
            );
        });
        ed.addCommand('mceShareoneDrive_links', function () {
            ed.windowManager.open(
                {
                    file: ajaxurl + '?action=shareonedrive-getpopup&type=links&callback=wpcp_add_content_to_mce',
                    width: 1024,
                    height: 600,
                    inline: 1
                },
                {
                    plugin_url: url
                }
            );
        });
        ed.addCommand('mceShareoneDrive_embed', function () {
            ed.windowManager.open(
                {
                    file: ajaxurl + '?action=shareonedrive-getpopup&type=embedded&callback=wpcp_add_content_to_mce',
                    width: 1024,
                    height: 600,
                    inline: 1
                },
                {
                    plugin_url: url
                }
            );
        });
        ed.addButton('shareonedrive', {
            title: 'Share-one-Drive shortcode',
            image: url + '/../../css/images/onedrive_logo.png',
            cmd: 'mceShareoneDrive'
        });
        ed.addButton('shareonedrive_links', {
            title: 'Share-one-Drive links',
            image: url + '/../../css/images/onedrive_link.png',
            cmd: 'mceShareoneDrive_links'
        });
        ed.addButton('shareonedrive_embed', {
            title: 'Embed Files from your OneDrive',
            image: url + '/../../css/images/onedrive_embed.png',
            cmd: 'mceShareoneDrive_embed'
        });

        ed.on('mousedown', function (event) {
            if (ed.dom.getParent(event.target, '#wp-sod-toolbar')) {
                if (tinymce.Env.ie) {
                    // Stop IE > 8 from making the wrapper resizable on mousedown
                    event.preventDefault();
                }
            } else {
                removeSodToolbar(ed);
            }
        });

        ed.on('mouseup', function (event) {
            var image,
                node = event.target,
                dom = ed.dom;

            // Don't trigger on right-click
            if (event.button && event.button > 1) {
                return;
            }

            if (node.nodeName === 'DIV' && dom.getParent(node, '#wp-sod-toolbar')) {
                image = dom.select('img[data-wp-sioselect]')[0];

                if (image) {
                    ed.selection.select(image);

                    if (dom.hasClass(node, 'remove')) {
                        removeSodToolbar(ed);
                        removeSODImage(image, ed);
                    } else if (dom.hasClass(node, 'edit')) {
                        var shortcode = ed.selection.getContent();
                        shortcode = shortcode.replace('</p>', '').replace('<p>', '').replace('[shareonedrive ', '').replace('"]', '');
                        var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
                        removeSodToolbar(ed);
                        ed.execCommand('mceShareoneDrive', query);
                    }
                }
            } else if (node.nodeName === 'IMG' && !ed.dom.getAttrib(node, 'data-wp-sioselect') && isSODPlaceholder(node, ed)) {
                addSodToolbar(node, ed);
            } else if (node.nodeName !== 'IMG') {
                removeSodToolbar(ed);
            }
        });

        ed.on('keydown', function (event) {
            var keyCode = event.keyCode;
            // Key presses will replace the image so we need to remove the toolbar
            if (sod_toolbarActive) {
                if (event.ctrlKey || event.metaKey || event.altKey || (keyCode < 48 && keyCode > 90) || keyCode > 186) {
                    return;
                }

                removeSodToolbar(ed);
            }
        });

        ed.on('cut', function () {
            removeSodToolbar(ed);
        });

        ed.on('BeforeSetcontent', function (ed) {
            ed.content = do_sod_shortcode(ed.content, t.url);
        });
        ed.on('PostProcess', function (ed) {
            if (ed.get) ed.content = get_sod_shortcode(ed.content);
        });
    });

    function do_sod_shortcode(co, url) {
        return co.replace(/\[shareonedrive([^\]]*)\]/g, function (a, b) {
            return (
                '<img src="' +
                url +
                '/../../css/images/transparant.png" class="wp_sod_shortcode mceItem" title="Share-one-Drive" data-mce-placeholder="1" data-code="' +
                toBinary(b) +
                '"/>'
            );
        });
    }

    function get_sod_shortcode(co) {
        function getAttr(s, n) {
            n = new RegExp(n + '="([^"]+)"', 'g').exec(s);
            return n ? n[1] : '';
        }

        return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function (a, im) {
            var cls = getAttr(im, 'class');

            if (cls.indexOf('wp_sod_shortcode') != -1)
                return '<p>[shareonedrive ' + tinymce.trim(fromBinary(getAttr(im, 'data-code'))) + ']</p>';

            return a;
        });
    }

    function removeSODImage(node, editor) {
        editor.dom.remove(node);
        removeSodToolbar(editor);
    }

    function addSodToolbar(node, editor) {
        var toolbarHtml,
            toolbar,
            dom = editor.dom;

        removeSodToolbar(editor);

        // Don't add to placeholders
        if (!node || node.nodeName !== 'IMG' || !isSODPlaceholder(node, editor)) {
            return;
        }

        dom.setAttrib(node, 'data-wp-sioselect', 1);

        toolbarHtml =
            '<div class="dashicons dashicons-edit edit" data-mce-bogus="1"></div>' +
            '<div class="dashicons dashicons-no-alt remove" data-mce-bogus="1"></div>';

        toolbar = dom.create(
            'div',
            {
                id: 'wp-sod-toolbar',
                'data-mce-bogus': '1',
                contenteditable: false
            },
            toolbarHtml
        );

        var parentDiv = node.parentNode;
        parentDiv.insertBefore(toolbar, node);

        sod_toolbarActive = true;
    }

    function removeSodToolbar(editor) {
        var toolbar = editor.dom.get('wp-sod-toolbar');

        if (toolbar) {
            editor.dom.remove(toolbar);
        }

        editor.dom.setAttrib(editor.dom.select('img[data-wp-sioselect]'), 'data-wp-sioselect', null);

        sod_toolbarActive = false;
    }

    function isSODPlaceholder(node, editor) {
        var dom = editor.dom;

        if (dom.hasClass(node, 'wp_sod_shortcode')) {
            return true;
        }

        return false;
    }
    function toBinary(str) {
        return btoa(
            encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function toSolidBytes(match, p1) {
                return String.fromCharCode('0x' + p1);
            })
        );
    }

    function fromBinary(str) {
        return decodeURIComponent(
            atob(str)
                .split('')
                .map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                })
                .join('')
        );
    }
})();
