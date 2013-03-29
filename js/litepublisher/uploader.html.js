(function ($, litepubl, window) {
  litepubl.HTMLUploader = litepubl.Uploader.extend({
jq: false,
queue: false,

init_handler: function() {
this.queue = [];
var self = this;
	$("#file-input, #dropzone").fileReaderJS({
		on: {
			load: function(e, file) {
self.queue.push(file);
if (self.queue.length == 1) self.start(file);
},

			beforestart: function(file) {
//alert('before');
//dump(file);
}
}
});
    },

start: function() {
if (this.queue.length) this.uploadfile(this.queue[0]);
},

next: function() {
if (this.queue.length) {
this.queue.shift();
this.start();
if (this.queue.length == 0) this.complete();
}
},

uploadfile: function(file) {
this.before(file);
var formdata = new FormData();
formdata.append("filedata", file);

for (var name in this.postdata) {
formdata.append(name, this.postdata[name]);
}

var self = this;
this.jq = $.ajax({
type: "post",
url: this.url,
cache: false,
data: formdata,
    contentType: false,
    processData: false,

        success: function(r) {
self.uploaded(r);
self.next();
},

  xhr: function() {
var result = $.ajaxSettings.xhr();
if ("upload" in result) {
    result.upload.addEventListener("progress", function(event){
      if (event.lengthComputable) {  
self.setprogress(event.loaded, event.total);
      }
    }, false); 

  //Download progress
/*
    result.addEventListener("progress", function(event){
      if (event.lengthComputable) {  
        var percentComplete = event.loaded / event.total;
      }
    }, false); 
*/
}
return result;
  }

})
          .fail( function(jq, textStatus, errorThrown) {
self.next();
});
}

  });
}(jQuery, litepubl, window));