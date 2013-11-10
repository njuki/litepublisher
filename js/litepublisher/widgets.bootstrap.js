/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document, litepubl){
litepubl.BootstrapWidgets = Class.extend({
inlineclass: ".widget-inline",
ajaxclass: ".widget-ajax",
toggleclass: "fa-expand fa-collapse",

init: function() {
var self = this;
  $(this.inlineclass).one('click', function() {
    self.addinline($(this));
    return false;
  });
  
  $(this.ajaxclass).one("click", function() {
   self.load($(this));
    return false;
  });
},

load: function(link) {
var widget = link.data("widget");
  var self = this;
  $.get(ltoptions.url + '/getwidget.htm', {
  id: widget.id,
   sidebar: widget.sidebar,
    themename: ltoptions.theme.name,
     idurl: ltoptions.idurl
     }, function (html) {
     widget.html = html;
     widget.comment = link.findcomment(widget.id);
    self.add(link);
 }, 'html');
},

addinline: function(link) {
var widget = link.data("widget");
widget.comment = link.findcomment(false);
  if (! widget.comment) return alert('Widget content not found');
  widget.html =  widget.comment.nodeValue;
        this.add(link);
},

add: function(link) {
var self = this;
  link.on("click.widget", function() {
    self.toggle($(this));
    return false;
  });
  
  link.find("i").toggleClass(this.toggleclass);
var widget = link.data("widget");
widget.action = link.parent().data("action");
switch (widget.action) {
case "popover":
if (widget.comment) $(widget.comment).remove();
widget.comment = false;

link.popover({
title: link.text(),
html: widget.html,
container: "body",
placement: link.parent().data("placement"),
trigger: "manual"
 });
break;

case "slide":
widget.body = $(widget.comment).replaceComment( widget.html);
widget.comment = false;
  }
  },

toggle: function(link) {
  link.find("i").toggleClass(this.toggleclass);
var widget = link.data("widget").body;
switch (widget.action) {
case "popover":
link.popover('toggle');
break;

case "slide":
widget.body.slideToggle();
}
}

});

$(document).ready(function() {
litepubl.widgets = new litepubl.BootstrapWidgets();
});
})( jQuery , window, document, litepubl);