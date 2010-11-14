/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var widgets = {
  items: []
};

widgets.load = function (node, id, sitebar) {
  var comment = widgets.findcomment(node, id);
  if (! comment) return alert('Widget not found');
        node.onclick = null;

$.get(ltoptions.url + '/getwidget.htm',
{id: id, sitebar: sitebar, themename: ltoptions.themename, idurl: ltoptions.idurl},

function (result) { 
      var content = $(result);
$(comment).replaceWith(content);
widgets.add(node, content);
    }, 'html');
}

widgets.inlineload= function (node) {
  var comment = widgets.findcomment(node, false);
  if (! comment) return alert('Widget not found');
      var content = $(comment.nodeValue);
$(comment).replaceWith(content);
  return widgets.add(node, content);
}

widgets.add = function(node, widget) {
  node.onclick = widgets.toggle;
  widgets.items.push([node, widget]);
  return widgets.items.length - 1;
}

widgets.setitem = function(node, value) {
  for (var i = widgets.items.length - 1; i >= 0; i--) {
    if (node == widgets.items[i][0]) {
      widgets.items[i][1] = value;
      return;
    }
  }
  widgets.add(node, value);
}

widgets.toggle = function() {
node = this;
    for (var i = widgets.items.length - 1; i >= 0; i--) {
      if (node == widgets.items[i][0]) {
    $(widgets.items[i][1]).toggle();
        return;
      }
    }
}

widgets.findcomment = function(node, id) {
var result = false;
if (id) id = 'widgetcontent-' + id;
do {
result = node;
  while (result = result.nextSibling) {
    if (result.nodeType  != 8)continue;
      if (!id || (id == result.nodeValue)) return result;
    }
} while (node = node.parentNode);
  return false;
}
