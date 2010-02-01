			var swfu;

function createswfu() {
var settings = {
				flash_url : ltoptions.files + "/js/swfupload/swfupload.swf",
				upload_url: ltoptions.url + "/admin/swfupload/",
 prevent_swf_caching: false,
				post_params: {"admincookie": getcookie("admin")},
				file_size_limit : "100 MB",
				file_types : "*.*",
				file_types_description : "All Files",
				file_upload_limit : 0,
				file_queue_limit : 0,
				custom_settings : {
					progressTarget : "fsUploadProgress",
					cancelButtonId : "btnCancel"
				},
				debug: true,

				// Button settings
				button_image_url: ltoptions.files + "/js/swfupload/images/XPButtonUploadText_61x22.png",
//				button_text: '<span class="theFont">Hello</span>',
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 61,
		button_height: 22,

				// The event handler functions are defined in handlers.js
//		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete
			};

try {
			swfu = new SWFUpload(settings);
 } catch(e) {
alert('Error! ' + e.message);
}
}
