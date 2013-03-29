(function ($, litepubl, window) {
window.litepubl.tml.uploader = {
html: '<div id="posteditor-fileperms" class="hidden"></div>\
    <div id="upload">\
	<input type="file" id="file-input" name="filedata" multiple />\
	<div id="dropzone">\
		Drag and drop files from your desktop here (or select them from the input above).\
	</div>\
</div>\
    <div id="progressbar"></div>',

htmlfile: "#file-input, #dropzone",
    progressbar: "#progressbar",

flash: '<div id="posteditor-fileperms" class="hidden"></div>\
    <div id="upload"><span id="uploadbutton"></span></div>\
    <div id="progressbar"></div>',

// without # for native javascript
flashbutton: "uploadbutton"
};

  litepubl.Uploader = Class.extend({
holder: false,
tml: false,
progressbar: false,
handler: false,
postdata: false,
url: "",
    maxsize: 100,
    types: "*.*",

    init: function(holder) {
this.holder = $(holder);
this.tml = litepubl.tml.uploader;
      this.items = new Array();
      this.url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
      var cookie = $.cookie("litepubl_user");
      if (!cookie) cookie = $.cookie("admin");

        this.postdata = {
          litepubl_user: cookie,
          litepubl_user_id: $.cookie("litepubl_user_id"),
          method: "files_upload"
        };

if ("FileReader" in window) {
this.holder.append(this.tml.html);
this.handler =  new litepubl.HTMLUploader(this);
} else {
this.holder.append(this.tml.flash);
this.handler = new litepubl.FlashUploader(this);
}

this.progressbar = $(this.tml.progressbar, this.holder);
},

setpercent: function(percent) {
        this.progressbar.progressbar({value: percent});
},

setprogress: function(current, total) {
this.setpercent(Math.ceil((current / total) * 100));
},

hideprogress: function() {
              this.progressbar.progressbar( "destroy" );
},

error: function(mesg) {
          $.messagebox(lang.dialog.error, mesg);
},

uploaded: function(resp) {
            var r = $.parseJSON(resp);

            this.items.push(resp);
$(this).trigger({
type: "upload",
resp: resp
});
},
    
    addparam: function(name, value) {
if ("addparam" in this.handler) {
this.handler.addparam(name, value);
} else {
this.postdata[name] = value;
}
},

addperm: function() {
      var perm = $("#combo-idperm_upload");
      if (perm.length) this.addparam("idperm", perm.val());
},

    before: function() {
this.addperm();
      $(this).trigger({
type: "onbefore",
uploader: this
});
    },
    
    complete: function() {
this.hideprogress();
      $(this).trigger({
type: "oncomplete",
uploader: this,
items: this.items
});
      this.items.length = 0;
    },

//events
    onbefore: function(fn) {
$(this).bind("onbefore", fn);
},

    onupload: function(fn) {
$(this).bind("onupload", fn);
},

    oncomplete: function(fn) {
$(this).bind("oncomplete", fn);
}

  });
}(jQuery, litepubl, window));