/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function getquotedcontent( authorname, content) {
  if (content == '') {
    return "2 [b]" + authorname + "[/b]: ";
  } else {
    return "[b]" + authorname + "[/b] " + lang.comment.says + ":\n[quote]" + content + "[/quote]\n";
  }
}

function quotecomment(id, authorname) {
  var formarea = document.getElementById("comment");
  var commentcontent = document.getElementById('commentcontent-' + id);
  
if (window.getSelection) {
 var sel = window.getSelection();
 } else if (document.getSelection) {
 var sel = document.getSelection(); 
} else if (document.selection) {
 var sel = document.selection.createRange().text; 
} else {
var sel = '';
}
  
  if (sel == '') {
  if (commentcontent.innerText){
sel = commentcontent.innerText; 
} else {
 sel = commentcontent.textContent; 
}
  }
  
  formarea.value += getquotedcontent(authorname, sel);
  formarea.focus();
}

function replycomment(id, authorname) {
  var formarea = document.getElementById("comment");
  formarea.value += getquotedcontent(authorname, '');
  formarea.focus();
}