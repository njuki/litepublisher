(function ($, litepubl, window) {
  litepubl.HTMLUploader = Class.extend({
owner: false,
jq: false,
queue: false,

init: function(owner) {
this.owner = owner;
this.queue = [];
var self = this;
	$(owner.tml.htmlfile, owner.holder).fileReaderJS({
accept: owner.mime,
		on: {
			load: function(e, file) {
self.queue.push(file);
if (self.queue.length == 1) self.start(file);
},

			beforestart: function(file) {
//litepubl.dump(file);
if (owner.maxsize && (file.size > (owner.maxsize * 1024 * 1024))) return false;
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
if (this.queue.length == 0) {
this.jq = false;
this.owner.complete();
}
}
},

uploadfile: function(file) {
var owner = this.owner;
owner.before(file);

var formdata = new FormData();
// warning: Filedata is same in flash and can not be changed
formdata.append("Filedata", file);

for (var name in owner.postdata) {
formdata.append(name, owner.postdata[name]);
}

var self = this;
this.jq = $.ajax({
type: "post",
url: owner.geturl(),
cache: false,
data: formdata,
    contentType: false,
    processData: false,

        success: function(r) {
owner.uploaded(r);
self.next();
},

  xhr: function() {
var result = $.ajaxSettings.xhr();
if ("upload" in result) {
    result.upload.addEventListener("progress", function(event){
      if (event.lengthComputable) {  
owner.setprogress(event.loaded, event.total);
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
owner.error(jq.responseText);
});
}

  });
}(jQuery, litepubl, window));