(function () {
	var args = custom_user_avatar_tinymce_editor_args || {};

	tinymce.create('tinymce.plugins.wpUserAvatar', {
		init: function (ed, url) {
			ed.addCommand('mceWpUserAvatar', function() {
				ed.windowManager.open({
					file:   ajaxurl + '?action=wp_user_avatar_tinymce',
					width:  500,
					height: 360,
					inline: 1
				}, {
					plugin_url: url,
				});
			});

			ed.addButton('wpUserAvatar', {
				title: ( typeof args.insert_avatar != 'undefined' ? args.insert_avatar : 'Insert Avatar' ),
				cmd:   'mceWpUserAvatar',
				image: url + '/../images/wpua-20x20.png',
				onPostRender: function() {
					var ctrl = this;

					ed.on('NodeChange', function(e) {
						ctrl.active(e.element.nodeName == 'IMG');
					});
				}
			});
		},
		createControl: function(n, cm) {
			return null;
		},
		getInfo: function () {
			return {
				longname:  'WP User Avatar 2.0',
				author:    'chrismoretti',
				authorurl: 'https://profiles.wordpress.org/chrismoretti/',
				infourl:   'https://profiles.wordpress.org/chrismoretti/',
				version:   '1.0',
			};
		},
	});

	tinymce.PluginManager.add('wpUserAvatar', tinymce.plugins.wpUserAvatar);
})();
