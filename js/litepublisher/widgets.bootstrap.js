/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document, litepubl){
litepubl.BootstrapWidgets = Class.extend({
toggleclass: "",

init: function(options) {
options = $.extend({
button: ".widget-button",
inline: ".widget-inline",
ajax: ".widget-ajax",
toggle: "fa-expand fa-collapse"
},options);

var self = this;
self.toggleclass = options.toggle;
$(options.button).each(function() {
var button = $(this);
var inline = button.find(options.inline);
if (inline.length) {
button.data("span", inline)
.one('click', function() {
    self.addinline($(this));
    return false;
  });
  return;
  }
  
  var ajax = button.find(options.ajax);
  if (ajax.length) {
  button.data("span", ajax)
.one("click", function() {
   self.load($(this));
    return false;
  });
  return;
  }
  
  //no ajax or inline, init necessary plugins
  switch (button.data("model")) {
  case "dropdown":
  button.dropdown();
    break;
    
      case "slide":
      button.on("click.widget", function() {
      $(this).next().slideToggle();
      return false;
      });
      break;
  
  case "popover":
  break;
      }
  });
},

load: function(button) {
var widget = button.data("span").data("widget");
  var self = this;
  $.get(ltoptions.url + '/getwidget.htm', {
  id: widget.id,
   sidebar: widget.sidebar,
    themename: ltoptions.theme.name,
     idurl: ltoptions.idurl
     }, function (html) {
     widget.html = html;
     widget.comment = button.findcomment(widget.id);
    self.add(button);
 }, 'html');
},

addinline: function(button) {
var widget = button.data("span").data("widget");
widget.comment = button.findcomment(false);
  if (! widget.comment) return alert('Widget content not found');
  widget.html =  widget.comment.nodeValue;
        this.add(button);
},

add: function(button) {
var widget = button.data("span").data("widget");
switch (button.data("model")) {
case "dropdown":
widget.body = $(widget.comment).replaceComment( widget.html);
widget.comment = false;
button.dropdown().dropdown("toggle");
break;

case "slide":
widget.body = $(widget.comment).replaceComment( widget.html);
widget.comment = false;
var self = this;
self.toggleicon(span);
button.data("body", widget.body)
.on("click.widget", function() {
var btn = $(this);
self.toggleicon(btn.data("span"));
btn.data("body").slideToggle();
return false;
});
break;

case "popover":
if (widget.comment) $(widget.comment).remove();
widget.comment = false;

var span = button.data("span");
this.toggleicon(span);
button.popover({
title: span.text(),
html: widget.html,
container: "body",
placement: button.data("placement"),
trigger: "manual"
 });
 
 var self = this;
 button.on("click.widget", function() {
 var btn = $(this);
 btn.popover('toggle');
 self.toggleicon(btn.data("span"));
 return false;
 });
 break;
  }
  },
  
  toggleicon: function(span) {
    span.find("i").toggleClass(this.toggleclass);
    }

});

$(document).ready(function() {
litepubl.widgets = new litepubl.BootstrapWidgets();
});
})( jQuery , window, document, litepubl);