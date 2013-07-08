/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$(document).ready(function(){
  $("#themetree").find("a").click(function() {
    try {
      $(this).parent().find("ul:first").slideToggle();
      var rel = $(this).attr("rel");
      if (rel == "ignore") return false;
      var e = $(this).data("editor");
      if (e) {
        switcheditor(e);
      } else {
        $(this).data("editor", createeditor(rel, $(this).text()));
      }
  } catch (e) { alert(e.message); }
    
    return false;
  });
});

function replace_string(s, src, dst) {
  var i = s.indexOf(src);
  while(i>-1){
    s = s.replace(src, dst);
    i = s.indexOf(src);
  }
  return s;
}

function createeditor(path, title) {
  var html = '<p><label for="$name"><strong>$lang.$name:</strong></label><br /> <textarea name="$name" id="$name" cols="57%" rows="10">$value</textarea></p>';
  
  html = replace_string(html, "$lang.$name", title);
  html = replace_string(html, "$name", replace_string(path, ".", "_"));
  var value = theme[path];
  if (value == undefined) alert(path);
  html = replace_string(html, "$value", value);
  
  var result = $(html);
  $("#themeeditor").append(result);
  switcheditor(result);
  return result;
}

function switcheditor(editor) {
  var current = $("#themeeditor").data("mycurrent");
  if (current) {
    if (current == editor) return;
    current.slideUp('fast', function() {
      editor.slideDown('fast');
    });
  }
  else editor.show();
  $("#themeeditor").data("mycurrent", editor);
}