/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function widget_load(node, id, sidebar) {
  $(node).attr("onclick", "");
  var comment = widget_findcomment(node, id);
  if (! comment) return alert('Widget not found');
  $.get(ltoptions.url + '/getwidget.htm',
{id: id, sidebar: sidebar, themename: ltoptions.theme.name, idurl: ltoptions.idurl},
  function (html) {
    var content = $(html);
    $(comment).replaceWith(content);
    widget_add(node, content);
  }, 'html');
}

function widget_findcomment(node, id) {
  var result = false;
  if (id) id = 'widgetcontent-' + id;
  do {
    result = node;
    while (result = result.nextSibling) {
      if (result.nodeType  == 8) {
        if (!id || (id == result.nodeValue)) return result;
      }
    }
  } while (node = node.parentNode);
  return false;
}

function widget_inline(node) {
  var comment = widget_findcomment(node, false);
  if (! comment) return alert('Widget not found');
  var content = $(comment.nodeValue);
  $(comment).replaceWith(content);
  widget_add(node, content);
}

function widget_add(node, widget) {
  $(node).data("litepublisher_widget", widget);
  $(node).click(function(event) {
    widget_toggle(this);
    return false;
  });
}

function widget_toggle(node) {
  $(node).data("litepublisher_widget").slideToggle();
}

$(document).ready(function() {
  $("*[rel~='inlinewidget']").one('click', function() {
    widget_inline(this);
    return false;
  });
  
  $(".inlinewidget, .ajaxwidget").each(function() {
    var a = $('<a class="expandwidget" href="">' + lang.widgetlang.expand + '</a>');
    $(this).append(a);
    a.one("click", function() {
      if ($(this).parent().hasClass("inlinewidget")) {
        widget_inline(this);
      } else {
        var rel = $(this).parent().attr("rel").split("-");
        widget_load(this, rel[1], rel[2]);
      }
      return false;
    });
    
    a.click(function() {
      $(this).toggleClass("expandwidget colapsewidget");
      $(this).text($(this).hasClass("expandwidget") ? lang.widgetlang.expand : lang.widgetlang.colapse);
      return false;
    });
    
  });
  $(".widget-load").one("click", function() {
    var self = $(this);
    widget_load(this, self.data("idwidget"), self.data("sidebar"));
    return false;
  });
});