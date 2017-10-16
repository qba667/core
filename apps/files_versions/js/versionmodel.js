/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function() {
	/**
	 * @memberof OCA.Versions
	 */
	var VersionModel = OC.Backbone.Model.extend({

		/**
		 * Restores the original file to this revision
		 */
		revert: function(options) {
			options = options ? _.clone(options) : {};
			var model = this;

			OC.Files.getClient().copy(this.getDownloadUrl(), this.getFullPath(), true)
				.done(function() {
					if (options.success) {
						options.success.call(options.context, model, response, options);
					}
					model.trigger('revert', model, response, options);
				})
				.fail(function () {
					if (options.error) {
						options.error.call(options.context, model, response, options);
					}
					model.trigger('error', model, response, options);
				});
		},

		getFullPath: function() {
			return this.get('fullPath');
		},

		getPreviewUrl: function() {
			var url = OC.generateUrl('/apps/files_versions/preview');
			var params = {
				file: this.get('fullPath'),
				version: this.get('timestamp')
			};
			return url + '?' + OC.buildQueryString(params);
		},

		getDownloadUrl: function() {
			return OC.linkToRemote('dav') + '/meta/' +
				encodeURIComponent(this.get('fileId')) + '/v/' +
				encodeURIComponent(this.get('versionId'));
		}
	});

	OCA.Versions = OCA.Versions || {};

	OCA.Versions.VersionModel = VersionModel;
})();

