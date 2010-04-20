/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var swfu;
var  createswfu = function() {
  if (swfu != undefined) return;
  var settings = {
    flash_url : ltoptions.files + "/js/swfupload/swfupload.swf",
    upload_url: ltoptions.url + "/admin/swfupload.htm",
    // prevent_swf_caching: false,
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
    //				debug: true,
    
    // Button settings
    button_image_url: ltoptions.files + "/js/swfupload/images/XPButtonUploadText_61x22.png",
    //button_text: '<span class="theFont">Hello</span>',
    button_placeholder_id : "uploadbutton",
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
    alert('Error create swfupload ' + e.message);
  }
}

function swfUploadPreLoad() {
  //alert('swfUploadPreLoad');
}

function swfUploadLoaded() {
  //alert('swfUploadLoaded');
}

function swfUploadLoadFailed() {
  //alert('swfUploadLoadFailed');
}

function fileQueued(file) {
  //alert('fileQueued');
}

function fileQueueError(file, errorCode, message) {
  //alert('fileQueueError');
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
  this.startUpload();
}

function uploadStart(file) {
  return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
}

function uploadSuccess(file, serverData) {
  post.add(serverData);
}

function uploadError(file, errorCode, message) {
  alert('uploadError');
}

function uploadComplete(file) {
  //alert('uploadComplete');
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
  //alert('queueComplete');
};

//callback
swfumutex.creator = true;
if (swfumutex.uploader) createswfu();