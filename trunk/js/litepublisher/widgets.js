/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function widget_load(node, id, sidebar) {
  $(node).attr("onclick", "");
  var comment = widget_findcomment(node, id);
  if (! comment) return alert('Widget not found');
  $.get(ltoptions.url + '/getwidget.htm',
{id: id, sidebar: sidebar, themename: ltoptions.themename, idurl: ltoptions.idurl},
  
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
});

$.extend({
	load_script: function( url, callback ) {
		return $.ajax({
			type: 'get',
			url: url,
			data: undefined,
			success: callback,
			dataType: "script",
cache: true
		});
	}
});

