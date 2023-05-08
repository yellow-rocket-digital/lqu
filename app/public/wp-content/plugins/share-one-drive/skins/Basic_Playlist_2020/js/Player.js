'use strict';

window.init_share_one_drive_media_player = function (listtoken, plugin) {
  var container = document.querySelector('.media[data-token="' + listtoken + '"]');

  /* Load Playlist via Ajax */
  var data = {
    action: 'shareonedrive-get-playlist',
    account_id: container.getAttribute('data-account-id'),
    drive_id: container.getAttribute('data-drive-id'),
    lastFolder: container.getAttribute('data-id'),
    sort: container.getAttribute('data-sort'),
    listtoken: listtoken,
    page_url: document.location.href,
    _ajax_nonce: ShareoneDrive_vars.getplaylist_nonce
  };

  jQuery.ajaxQueue.addRequest({
    type: "POST",
    url: ShareoneDrive_vars.ajax_url,
    data: data,
    success: function (json) {
      var playlist = create_playlistfrom_json(json);
      init_mediaelement(container, listtoken, playlist, plugin);

      const event = new CustomEvent('ajax-success', {
        detail: {
          json: json,
          request: data
        }
      });

      container.dispatchEvent(event);

    },
    error: function (json) {
      container.querySelector('.loading.initialize').style.display = 'none';
      container.querySelector('.wpcp__main-container').classList.add('error');

      const event = new CustomEvent('ajax-error', {
        detail: {
          json: json,
          request: data
        }
      });

      container.dispatchEvent(event);

    },
    dataType: 'json'
  });

  jQuery.ajaxQueue.run();
}