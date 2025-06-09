(function($) {
$('.dropzone-holder.backend').entwine({

	IFrameUrl: null,

	IFrameParams: null,

	getConfig: function (opt) {
		var config = this.data('config');

		return opt ? config[opt] : config;
	},

	requestFileTemplates: function (ids) {
		if(!ids || !ids.length) return;
		var self = this;
		$.ajax({
			url: this.getIFrameUrl()+'/filesbyid?ids='+ids.join(',') + '&' + this.getIFrameParams(),
			dataType: 'JSON',
			success: function (json) {
				json.forEach(function(item) {
					self.attachFile(item.id, item.html);
				});
			}
		});
	},

	attachFile: function (id, html) {
		var uploader = this.data('dropzoneInterface');
		var DroppedFile = this.data('dropzoneFile');
		var file = new DroppedFile(uploader, {serverID: id});
		var $li = $(html);

		if(!uploader.settings.uploadMultiple) {
			uploader.clear();
		}

		uploader.droppedFiles.push(file);
		file.createInput();
		$(uploader.settings.previewsContainer).append($li);
		uploader.bindEvents($li[0], id);
	}
});

$('.file-attachment-field-previews .file-icon, .file-attachment-field-previews .file-meta').entwine({
	onclick: function(e) {
		e.preventDefault();

		var parent = $(this).parents('li');

		window.open(parent.data('file-link') , '_blank');
	}
})

})(jQuery);
