(function( $, window, document){
  'use strict';
  
  $.LitepublImages = Class.extend({
    dataname: "lpimg",
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
    dlg_width: 32,
    dlg_height: 152,
    
    dialog: false,
    opened: false,
    
    /* events callbacks
    onopen
    onclose
    onindex
    onimage
    onanimated
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
    loading_html: '<img src="" data-src="modal-galery-loading" class="hide img-galery" alt="" />',
    //loading_html: '<img src="/js/litepublisher/images/loader-128x/Preloader_1.gif" data-src="modal-galery-loading" class="hide img-galery" alt="" />',
    thumb_html: '<a href="#" class="thumbnail" data-index="%%index%%"><img src="%%url%%" /></a>',
    
    style_html:'<style type="text/css">' +
    '.modal-galery{' +
      'width:%%dlg_width%%px;' +
      'height:%%dlg_height%%px' +
    '}' +
    '.modal-galery .modal-body{' +
      'width:%%body_width%%px;' +
      'height:%%body_height%%px' +
    '}</style>',
    
    init: function(items) {
      this.items = [];
      this.linkattrs = ['href', 'data-image', 'data-src'];
      this.is_image = new RegExp(this.is_image, 'i');
      this.default_title = $("title").text();
      
      this.onopen = $.Callbacks();
      this.onclose = $.Callbacks();
      this.onindex= $.Callbacks();
      this.onimage = $.Callbacks();
      this.onanimated = $.Callbacks();
      
      
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
      if (this.dialog.style) this.dialog.style.remove();
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
      location.hash = "!" + ((this.dialog && this.dialog.group) || this.dataname) + "/" + index;
    },
    
    openhash: function() {
      var hash = this.gethash();
      if (!hash) return false;
      var a = hash.split('/');
      if (a.length <= 1) return false;
      
      var index = parseInt(a[1]);
      if ((index < 0) || (index >= this.items.length))  return false;
      
      var group = a[0];
      if (group.length && group.substring(0, 1) == "!") group = group.substring(1, group.length);
      
      var curitems = this.getcuritems(group);
      if (index >= curitems.length) return false;
      return this.open(curitems[index], curitems);
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
      var width, height, ratio, minratio = 999, maxratio = 0, maxwidth = 0, maxheight = 0, loaded = true;
      
      //if all images loaded then we can setmax size and define real ratio
      for (var i = curitems.length - 1; i >= 0; i--) {
        var item = curitems[i];
        if (item.ready) {
          maxwidth = Math.max(maxwidth, item.width);
          maxheight = Math.max(maxheight, item.height);
          ratio = item.width / item.height;
          maxratio = Math.max(maxratio, ratio);
          minratio = Math.min(minratio, ratio);
        } else {
          loaded = false;
          break;
        }
      }
      
      var win_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
      var win_height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
      
      if (typeof this.width === "number") {
        width = this.width;
      } else if (typeof this.width === "string" && this.width.indexOf('%') > 0) {
        width = parseInt(this.width) * win_width / 100;
      }
      
      if (!this.height) {
        height = width * this.ratio;
      } else if (typeof this.height === "number") {
        height= this.height;
      } else if (typeof this.height === "string" && this.height.indexOf('%') > 0) {
        height = parseInt(this.height) * win_height / 100;
      }
      
      if (loaded) {
        if (width > maxwidth) width = maxwidth;
        if (maxratio == minratio) height = width / minratio;
        if (height > maxheight) height = maxheight;
      }
      
      //30 = 2*15px bounds
      width = Math.floor(Math.min(width,  win_width - this.dlg_width  - 30));
      height = Math.floor(Math.min(height, win_height - this.dlg_height - 30));
      
      return $.simpletml(this.style_html, {
        dlg_width: width + this.dlg_width,
        dlg_height: height + this.dlg_height,
        body_width: width,
        body_height: height
      });
    },
    
    init_dialog: function(curitems) {
      var html = this.parse(this.html, {
        thumbnails: this.getthumbnails(curitems),
        social_buttons: this.social_buttons,
        player_buttons: this.player_buttons
      });
      
      html = this.parse(html, {
        id: $.now(),
        lang: lang.images,
        close: lang.dialog.close
      });
      this.speed = $.fx ? $.fx.speeds[this.speed] || this.speed : this.speed;
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
        style: style,
        loading: false
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
        var button = $(this);
        switch (button.attr("data-action")) {
          case "next":
          self.setindex(self.getindex() + 1);
          break;
          
          case "back":
          self.setindex(self.getindex() - 1);
          break;
          
          case "play":
          button.attr("disabled", "disabled");
          button.parent().find("[data-action='stop']").removeAttr("disabled");
          self.play();
          break;
          
          case "stop":
          button.attr("disabled", "disabled");
          button.parent().find("[data-action='play']").removeAttr("disabled");
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
      .on("hide.bs.modal", $.proxy(this.doclose, this))
      .on("hidden.bs.modal", $.proxy(this.doclosed, this))
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
      this.dialog.dialog.modal("hide");
    },
    
    doclose: function() {
      if (!this.dialog || !this.opened) return false;
      this.opened = false;
      this.stop();
      
      this.dialog.footer.find("button").each(function() {
        var button = $(this);
        var tooltip = button.data("bs.tooltip");
        if (tooltip) tooltip.hide();
        
        var popover = button.data("bs.popover");
        if (popover) popover.hide();
      });
    },
    
    doclosed: function() {
      if (this.hash_enabled) location.hash = "!" + this.dataname;
      this.dialog.style.remove();
      this.dialog.style = false;
      this.onclose.fire();
    },
    
    open: function (item, curitems) {
      if (!item || item.error) return false;
      if (this.opened) return false;
      this.opened = true;
      
      if (!item.ready && !item.error) this.load(item, false);
      
      if (!curitems) curitems = this.getcuritems(item.group);
      if (this.dialog) {
        var dialog = this.dialog;
        dialog.items = curitems;
        dialog.images = [];
        dialog.images.length = curitems.length;
        dialog.body.empty();
        dialog.thumbnails.html(this.getthumbnails(curitems));
        dialog.style = $(this.getstyle(curitems)).appendTo("head:first");
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
        if (item.error) continue;
        if ((item.group == group) || (!item.group && group == this.dataname) || (!group && item.group == this.dataname)) result.push(item);
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
        this.load(item, function() {
          if (self.opened) self.setimage(index, oldindex);
        });
        
        //last chance 60ms to prevent blinking loading icon
        setTimeout(function() {
          if (!item.ready && self.opened) self.showloading();
        }, 60);
      }
    },
    
    setimage: function(index, oldindex) {
      this.hideloading();
      this.dialog.title.text(this.dialog.items[index].title);
      if (oldindex >= 0) {
        this.deactivate(this.dialog.images[oldindex]);
        this.activate(this.getimage(index), $.proxy(this.animated, this));
      } else {
        var img = this.getimage(index);
        if (img) img.removeClass("hide").addClass(this.activeclass);
      }
      
      if (this.hash_enabled) this.sethash(index);
      this.preload();
      this.onimage.fire(index);
    },
    
    getimage: function(index) {
      if (index < 0  || index >= this.dialog.items.length) return false;
      var result = this.dialog.images[index];
      if (result) return result;
      
      var item = this.dialog.items[index];
      if (!item.ready) return false;
      
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
    
    deactivate: function(img, callback) {
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
    
    activate: function(img, callback) {
      if (!img) return false;
      img.removeClass("hide").addClass(this.activeclass);
      if ($.support.transition) {
        img.addClass(this.transclass);
        if (callback) setTimeout(callback, this.speed);
      } else {
        img.stop(true, true)
        .fadeIn(this.speed, callback);
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
      this.onanimated.fire();
      
      if (this.opened && this.playnext) {
        setTimeout(this.playnext, 60);
      }
    },
    
    playindex: function() {
      if (this.opened) this.setindex(this.getindex() + 1);
    },
    
    play: function() {
      if (this.dialog.items.length  <= 1) return false;
      this.circle = true;
      this.playnext = $.proxy(this.playindex, this);
      this.playnext();
    },
    
    stop: function() {
      this.circle = false;
      this.playnext = false;
    },
    
    showloading: function() {
      var img = this.dialog.loading;
      if (!img) {
        var body = this.dialog.body;
        img = this.dialog.loading = $(this.loading_html).appendTo(body);
        if (img.is("img") && !img.attr("src")) {
          var src = img.attr("data-src");
          img.addClass(src);
          img.attr("src", img.css("backgroundImage"));
          var w = img.css("width");
          var h = img.css("height");
          img.removeClass(src);
          
          img.css({
            width: w,
            height: h,
            left: Math.floor((body.width() - w) / 2),
            top: Math.floor((body.height() - h) / 2)
          });
        } else {
          img.css({
            left: Math.floor((body.width() - img.width()) / 2),
            top: Math.floor((body.height() - img.height()) / 2)
          });
        }
      }
      
      if (img.hasClass("hide")) this.activate(img);
    },
    
    hideloading: function() {
      var img = this.dialog.loading;
      if (img && !img.hasClass("hide")) this.deactivate(img);
    }
    
  });
  
  $(document).ready(function() {
    $.litepublImages = new $.LitepublImages ("a.photo");
  });
  
}(jQuery, window, document));