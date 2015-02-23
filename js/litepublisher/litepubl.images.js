(function( $, window, document){
  'use strict';

$.LitepublImages = Class.extend({
dataname: "modimg",
items: false,
activeclass: "img-galery-active",
transclass: "img-galery-trans",
cursorclass: "cursor-progress",
speed: "slow",
circle: false,
playnext: false,
default_title: "",
lang: "en",
facebook_appid: "290433841025058",
facebook_closeurl: "/close-window.html",
hash_enabled: true,
popover_enabled: true,
    is_image: '\\.(jpg|jpeg|png|bmp|webp|svg)$',
linkattrs: false,

ratio: 4 / 3,
      width: '80%',
      height: false,
//width beetwin dialog body and padding 15 + border
minwidth: 32, 
minheight: 152,

dialog: false,
opened: false,

/* events callbacks
onopen
onclose
onindex
onimage
*/

   html: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modal-title-%%id%%">' +
    '<div class="modal-dialog modal-galery"><div class="modal-content">' +
    '<div class="modal-header">' +
'<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span></button>' +
    '<h4 class="modal-title" id="modal-title-%%id%%"></h4>' +
'</div>' +
    '<div class="modal-body"></div>' +
//'<div class="modal-thumbnails">%%thumbnails%%</div>' +
    '<div class="modal-footer">' +
'%%social_buttons%% %%player_buttons%%' +
'</div>' +
    '</div></div></div>',

social_buttons: '<a role="button" target="_blank" href="#" class="btn btn-default social-button" data-like="facebook" title="%%lang.facebook%%"><span class="fa fa-facebook"></span> <span class="sr-only">FaceBook</span></a> ' +

'<a role="button" target="_blank" href="#" class="btn btn-default social-button" data-like="twitter" title="%%lang.twitter%%"><span class="fa fa-twitter"></span> <span class="sr-only">Twitter</span></a> ',

player_buttons: '<button type="button" class="btn btn-default player-button" data-action="back" title="%%lang.tipback%%"><span class="fa fa-backward"></span> <span class="sr-only">%%lang.backward%%</span></button> ' +

'<button type="button" class="btn btn-default player-button" data-action="next" title="%%lang.tipnext%%"><span class="fa fa-forward"></span> <span class="sr-only">%%lang.forward%%</span></button> ' +

'<button type="button" class="btn btn-default player-button" data-action="stop" title="%%lang.tipstop%%"><span class="fa fa-stop"></span> <span class="sr-only">%%lang.stop%%</span></button> ' +

'<button type="button" class="btn btn-default player-button" data-action="play" title="%%lang.tipplay%%"><span class="fa fa-play"></span> <span class="sr-only">%%lang.play%%</span></button> ',

img_html: '<img src="%%url%%" class="img-galery" width="%%width%%" height="%%height%%" style="left:%%left%%px" alt="" />',
thumb_html: '<a href="#" class="thumbnail" data-index="%%index%%"><img src="%%url%%" /></a>',

init: function(items) {  
this.items = [];
this.linkattrs = ['href', 'data-image', 'data-src'];
this.is_image = new RegExp(this.is_image, 'i');
this.default_title = $("title").text();

      this.onopen = $.Callbacks();
      this.onclose = $.Callbacks();
      this.onindex= $.Callbacks();
      this.onimage = $.Callbacks();

this.additems(items);
if (this.hash_enabled && this.items.length) this.openhash();
},

destroy: function() {
if (this.dialog) {
if (this.opened) {
this.dialog.one("hidden.bs.modal", $.proxy(this.removeDialog, this));
this.close();
} else {
this.removeDialog();
}
}

this.items = [];
},

removeDialog: function() {
if (!this.dialog) return false;

//manual destroy tooltips and popovers assigned in dialog
this.dialog.footer.find("button").each(function() {
var button = $(this);
var tooltip = button.data("bs.tooltip");
if (tooltip) {
clearTimeout(tooltip.timeout);
 if (("$tip" in tooltip) && tooltip .$tip) tooltip.$tip.remove();
}

var popover = button.data("bs.popover");
if (popover) {
clearTimeout(popover.timeout);
if (("$tip" in popover) && popover.$tip) popover.$tip.remove();
}
});

this.dialog.dialog.remove();
this.dialog.style.remove();
this.dialog = false;
},

load: function(item, callback) {
if (!item || item.error) return false;
if (item.ready)  return callback(item);

//image loading
if ("onready" in item) {
if (callback) item.onready.push(callback);
return;
}

item.onready = callback ? [callback] : [];

var img = new Image();
        img.onload = function(){
				this.onload = this.onerror = null;
item.width = this.width;
item.height = this.height;
item.ready = true;

var onready = item.onready;
for (var i = 0; i < onready.length; i++) {
var fn = onready[i];
fn(item);
}

delete item.onready;
};

        img.onerror = function(){
				this.onload = this.onerror = null;
item.error = true;
delete item.onready;
};

img.src = item.url;
},

extracturl: function(link) {
var url, attr;
for (var i = 0, l =this.linkattrs.length; i < l; i++) {
      url = link.attr(this.linkattrs[i]);
      if (url && this.is_image.test(url)) return url;
      }

return false;
},

extracttitle: function(link) {
var title = link.attr("title") || link.attr("data-title");
if (title && !this.is_image.test(title)) return title;

var img = link.find("img");
if (img.length) {
title = img.attr("alt") || img.attr("title");
if (title && !this.is_image.test(title)) return title;
}

title = link.text();
if (title && !this.is_image.test(title)) return title;

return this.default_title;
},

extractthumb:function(link) {
var img = link.find('img');
if (img.length) return img.attr("src");

var url = link.attr("data-thumb");
if (url && this.is_image.test(url)) return url;

//thumb mybe in background sprite 
var backgroundImage = link.css("backgroundImage");
if (backgroundImage) {
return {
backgroundImage: backgroundImage,
backgroundPositionX: link.css("backgroundPositionX"),
backgroundPositionY: link.css("backgroundPositionY"),
width: link.css("width"),
height: link.css("height")
};
}

return false;
},

additems: function(items) {
var self = this;
$(items).each(function() {
self.addlink(this);
});
},

addlink: function(alink) {
var link = $(alink);
if (link.data(this.dataname)) return;

var url = this.extracturl(link);
if (!url) return;

var item = {
url: url,
title: this.extracttitle(link),
thumb: this.extractthumb(link),
group: link.attr("data-galery") || link.attr("data-group") || link.attr("rel"),
width: 0,
height: 0,
ready: false,
error: false,
afterevent: false
};


this.items.push(item);
      link.data(this.dataname, item);

var self = this;
link.on("click." + this.dataname, function() {
var t = $(this);
if (self.popover_enabled) t.popover("hide");
self.open(t.data(self.dataname));
return false;
})
.on("mouseenter.init" + this.dataname + " focus.init" + this.dataname, function() {
var t = $(this);
t.off(".init" + self.dataname);
var data = t.data(self.dataname);
if (!self.popover_enabled) {
if (!data.ready && !data.error) self.load(data, false);
} else if (!self.opened) {
if (data.ready) {
self.init_popover(t);
} else if (!data.error){
t.addClass(self.loadingcursor);
data.afterevent = event.type;
t.one((event.type == "mouseenter" ? "mouseleave" : "blur") + ".after" + self.dataname, function() {
$(this).removeClass(self.loadingcursor).data(self.dataname).afterevent = false;
});

self.load(data, function() {
t.removeClass(self.loadingcursor);
self.init_popover(t);
});
}
}
});

},

init_popover: function(link) {
var data = link.data(this.dataname);
if (!data || !data.ready) return false;

          var ratio = data.width / data.height;
            if (ratio >= 1) {
              //horizontal image, midle height and maximum width
              var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
              h = Math.floor(h / 2) - 20;
              var w = Math.floor(h * this.ratio);
            } else {
              //vertical image, midle width and maximum height
              var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
              w = Math.floor(w / 2) - 20;
              var h = Math.floor(w / this.ratio);
            }

          if ((data.width <= w) && (data.height <= h)) {
            w = data.width;
            h = data.height;
          } else {
            if (w /h > ratio) {
              w = Math.floor(h *ratio);
            } else {
              h = Math.floor(w / ratio);
            }
          }
          
          var title = data.title;
          if (this.is_image.test(title)) title = this.default_title;
          
          link.popover({
            container: 'body',
            content: '<img src="' + data.url + '" width="' + w + '" height="' + h + '" />',
            delay: 120,
            html:true,
            placement: 'auto ' + (ratio >= 1 ? 'bottom' : 'right'),
            template: '<div class="popover popover-image"><div class="arrow"></div>' +
            '<h3 class="popover-title" style="max-width:' + w + 'px;"></h3>' +
            '<div class="popover-content"></div></div>',
            title: title,
            trigger: "hover"
          });

//show popover after load image if not lose focus or hover
          if (data.afterevent) link.trigger(data.afterevent);
},

    gethash: function() {
      var url = location.href;
      var i = url.indexOf('#');
      if (i == -1) return false;
      return decodeURI(url.substring(i+1,url.length));
    },
    
    sethash: 	function(index) {
      location.hash = "!" + this.dataname + '/' + index;
    },

openhash: function() {
var hash = this.gethash();
      if (!hash) return false;

var a = hash.split('/');
if (a.length <= 1) return false;
var index = parseInt(a[1]);
if ((index >= 0) && (index < this.items.length)) {
this.items[index].click();
}
},

parse: function(s, view) {
s = s.replace(/%%(\w*)\.(\w*)%%/gim, function(str, obj, prop, offset, src) {
      if ((obj in view) && (typeof view[obj] === "object") && (prop in view[obj])) return view[obj][prop];
      return str;
    });
    
return s.replace(/%%(\w*)%%/gim, function(str, prop, offset, src) {
      if (prop in view) return view[prop];
      return str;
    });
  },

getstyle: function(curitems) {
var width, height, css_width, css_height, maxwidth = 0, maxheight = 0;

//if all images loaded then we can setmax size
for (var i = curitems.length - 1; i >= 0; i--) {
var item = curitems[i];
if (item.ready) {
maxwidth = Math.max(maxwidth, item.width);
maxheight = Math.max(maxheight, item.height);
} else {
maxwidth = maxheight = 0;
break;
}
}


if (maxwidth) {
maxwidth += this.minwidth;
maxheight += this.minheight;
}

css_width = this.width;
css_height = this.height;

if (typeof this.width === "number") {
width = this.width;
if (maxwidth && (width > maxwidth)) width = maxwidth;
css_width = width + "px";
if (this.height) {
css_height =typeof this.height === "number" ? this.height + "px" : this.height;
} else {
var win_height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
height = Math.min(win_height - 60, Math.floor((width  - 60) /this.ratio) + this.minheight);
if (maxheight && (height > maxheight)) height = maxheight;
css_height = height + "px";
}
} else if (typeof this.width === "string" && this.width.indexOf('%') > 0 && !this.height) {
var win_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
var percent = win_width / 100;
height = Math.floor(Math.min(100 - this.minwidth / percent, (parseInt(this.width) - this.minwidth / percent) / this.ratio + this.minheight / percent));
css_height = height + "%";
}

if (maxwidth) {
css_width = css_width + ";max-width:" + maxwidth + "px";
css_height = css_height + ";max-height:" + maxheight + "px";
}

return '<style type="text/css">.modal-galery{width:' + css_width + ';height:' + css_height + '}</style>';
},

init_dialog: function(curitems) {
var html = this.parse(this.html, {
thumbnails: this.getthumbnails(curitems),
social_buttons: this.social_buttons,
player_buttons: this.player_buttons
});

html = this.parse(html, {
id: $.now(),
lang: lang.galery,
close: lang.dialog.close
});

var style = $(this.getstyle(curitems)).appendTo("head:first");
var dialog = $(html).appendTo("body");
this.dialog = {
index: -1,
items: curitems,
images: [],
dialog: dialog,
title: dialog.find(".modal-title"),
body: dialog.find(".modal-body"),
thumbnails: dialog.find(".thumbnails"),
footer: dialog.find(".modal-footer"),
style: style
};

this.dialog.images.length = curitems.length;

//assign events
this.init_social();
var self = this;

this.dialog.thumbnails.on("click.thumbnail", ".thumbnail", function() {
self.setindex($(this).attr("data-index"));
return false;
});

var footer = this.dialog.footer;
$(".player-button", footer).tooltip({
            container: 'body',
            placement: 'top'
          })
.on("click.galery", function() {
switch ($(this).attr("data-action")) {
case "next":
self.setindex(self.getindex() + 1);
break;

case "back":
self.setindex(self.getindex() - 1);
break;

case "play":
self.play();
break;

case "stop":
self.stop();
break;
}

return false;
});

this.dialog.body.on("click.prevnext", "img", function(e) {
var img = $(this);
        var x = e.pageX - img.offset().left;
if (x < img.width() / 2) {
self.stop();
self.setindex(self.getindex() - 1);
} else {
self.stop();
self.setindex(self.getindex() + 1);
}
								e.preventDefault();
});

      dialog.on("shown.bs.modal", function() {
self.opened = true;
self.onopen.fire();
})
.on("hidden.bs.modal", function() {
self.opened = false;
if (self.hash_enabled) location.hash = "!" + self.dataname;
self.onclose.fire();
})
      .modal();
},

init_social: function() {
var self = this;
if ("poplike" in $.fn) {
this.dialog.footer.find(".social-button").poplike({
network: function() {
return $(this).attr("data-like");
},

url: function() {
return self.geturl();
},

title: function() {
return self.gettitle();
},

placement: 'top',
lang: this.lang
});
} else {
this.dialog.footer.find(".social-button").tooltip({
            container: 'body',
            placement: 'top'
          })
.on("click.like", function() {
var index = self.getindex();
var item = self.dialog.items[index];
        var url= encodeURIComponent(location.href);
        var title= encodeURIComponent(item.title);
        var image = encodeURIComponent(item.url);

var t = $(this);
switch (t.attr("data-like")) {
case 'facebook':
if (self.facebook_appid) {
var href = 'https://www.facebook.com/dialog/feed?' + 
'app_id=' + self.facebook_appid +
 '&link=' + url +
'&name=' + title +
'&picture=' + image +
'&display=popup' +
'&redirect_uri=' + self.facebook_closeurl;
} else {
var href = 'https://www.facebook.com/sharer.php?u=' + url + '&t=' + title;
}
break;

case 'twitter':
var href = 'https://twitter.com/share?lang=' + self.lang + '&url=' + url + '&text=' + title;
break;

case 'vk':
var href = 'http://vk.com/share.php?url=' + url;
break;

case 'ok':
var href = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' + url + '&st.comments=' + title;
break;

default:
return false;
}

if (t.is("a")) {
t.attr("href", href);
t.attr("target", "_blank");
/*
function popclick() {
    document.getElementById("my_link").dispatchEvent((
function(e){
// type, canBubble, cancelable, view 
      e.initMouseEvent("click", true, true, window, 
              // detail, screenX, screenY, clientX, clientY 
            0, 0, 0, 0, 0,
 // ctrlKey, altKey, shiftKey, metaKey 
            false, false, false, false,
                   // button, relatedTarget 
            0, null);

      return e;
    }(document.createEvent('MouseEvents'))
));
  }

*/

} else {
window.open(href, '_blank');
return false;
}
});
}

},

close: function() {
if (!this.dialog || !this.opened) return false;
this.dialog.footer.find("button").each(function() {
var button = $(this);
var tooltip = button.data("bs.tooltip");
if (tooltip) tooltip.hide();

var popover = button.data("bs.popover");
if (popover) popover.hide();
});

this.dialog.dialog.modal("hide");
},

open: function (item) {
if (!item || item.error) return false;
if (this.opened) return false;
this.opened = true;

if (!item.ready && !item.error) this.load(item, false);

	this.speed = $.fx ? $.fx.speeds[this.speed] || this.speed : this.speed;

var curitems = this.getcuritems(item.group);
if (this.dialog) {
var dialog = this.dialog;
dialog.items = curitems;
dialog.images = [];
dialog.images.length = curitems.length;
dialog.body.empty();
dialog.thumbnails.html(this.getthumbnails(curitems));
dialog.index = -1;
this.setindex(this.urlindex(item.url));
dialog.dialog.modal("show");
} else {
this.init_dialog(curitems);
this.setindex(this.urlindex(item.url));
}
},

getcuritems: function(group) {
var result = [];
for (var i = 0, l = this.items.length; i < l; i++) {
var item = this.items[i];
if (item.group == group && !item.error) result.push(item);
}

return result;
},

getthumbnails: function(items) {
var result = "";
for (var i = 0, l = items.length; i < l; i++) {
result += this.parse(this.thumb_html, {
index: i,
url: items[i].url
});
}

return result;
},

geturl: function() {
return this.dialog.items[this.dialog.index].url;
},

gettitle: function() {
return this.dialog.items[this.dialog.index].title;
},

urlindex: function(url) {
var items = this.dialog.items;
for (var i = 0, l = items.length; i < l; i++) {
if (url == items[i].url) return i;
}

return false;
},

getindex: function() {
return this.dialog.index;
},

setindex: function(value) {
var index = parseInt(value);
var items = this.dialog.items;
if (index < 0) index = this.circle ? items.length - 1 : 0;
if (index >= items.length) index = this.circle ? 0 : items.length - 1;
if (index == this.dialog.index) return false;

var oldindex = this.dialog.index;
this.onindex.fire(index, oldindex);
this.dialog.index = index;
var item = items[index];
if (item.ready) {
this.setimage(index, oldindex);
} else {
var self = this;
self.setloading(true);
this.load(item, function() {
self.setloading(false);
self.setimage(index, oldindex);
});
}
},

setimage: function(index, oldindex) {
this.dialog.title.text(this.dialog.items[index].title);
if (oldindex >= 0) {
this.deactivate(oldindex);
this.setactive(index);
} else {
this.getimage(index).addClass(this.activeclass);
}

this.preload();
this.onimage.fire(index);
},

getimage: function(index) {
if (index < 0  || index >= this.dialog.items.length) return false;
var result = this.dialog.images[index];
if (result) return result;

var item = this.dialog.items[index];
var body = this.dialog.body;
      var bodywidth = body.width();
var width = bodywidth;
      var height = this.dialog.body.height();
if (item.width <= width && item.height <= height) {
width = item.width;
height = item.height;
} else {
      var ratio = item.width / item.height;
      if (width/height > ratio) {
        width = Math.floor(height *ratio);
      } else {
        height = Math.floor(width / ratio);
      }
}
      
var html = $.simpletml(this.img_html, {
url: item.url,
width: width,
height:height,
left: Math.floor((bodywidth - width) / 2)
});

return this.dialog.images[index] = $(html).appendTo(body);
},

deactivate: function(index) {
var img = this.dialog.images[index];
if (!img) return false;

img.removeClass(this.activeclass);
if ($.support.transition) {
img.removeClass(this.transclass);
setTimeout(function() {
img.addClass("hide");
}, this.speed);
} else {
img.stop(true, true)
.fadeOut(this.speed, function() {
$(this).addClass("hide");
});
}
},

setactive: function(index) {
var img = this.getimage(index);
img.removeClass("hide").addClass(this.activeclass);

if ($.support.transition) {
img.addClass(this.transclass);
setTimeout($.proxy(this.animated, this), this.speed);
} else {
img.stop(true, true)
      .fadeIn(this.speed, $.proxy(this.animated, this));
}
},

preload: function() {
var items = this.dialog.items;
for( var i = this.dialog.index + 1, l = items.length; i < l; i++) {
var item = items[i];
if (!item.ready && !item.error) {
this.load(item, false);
break;
}
}

for (var i = this.dialog.index - 1; i >= 0; i--) {
var item = items[i];
if (!item.ready && !item.error) {
this.load(item, false);
break;
}
}
},

animated: function() {
if (this.playnext) this.playnext();
this.onanimated.fire();
},

play: function() {
if (this.dialog.items.length  <= 1) return false;
this.circle = true;
var self = this;
this.playnext = function() {
self.setindex(self.getindex() + 1);
};

this.playnext();
},

stop: function() {
this.circle = false;
this.playnext = false;
}

});

$(document).ready(function() {
$.litepublImages = new $.LitepublImages ("a.photo");
});

}(jQuery, window, document));