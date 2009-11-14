<script type="text/javascript">

function getquotedcontent( authorname, content) {
if (content == '') {
return "2 [b]" + authorname + "[/b]: ";
} else {
return "[b]" + authorname + "[/b] %2$s:\n[quote]" + content + "[/quote]\n";
}
}

function quotecomment(id, authorname) {
  var formarea = document.getElementById("%1$s");
  var commentcontent = document.getElementById('commentcontent-' + id);

  if (window.getSelection) { var sel = window.getSelection(); }
  else if (document.getSelection) { var sel = document.getSelection(); }
  else if (document.selection) { var sel = document.selection.createRange().text; }

	  if (sel == "") {
  if (commentcontent.innerText){sel = commentcontent.innerText; }
		else { sel = commentcontent.textContent; }
}

formarea.value += getquotedcontent(authorname, sel);
  formarea.focus();
}

function replycomment(id, authorname) {
  var formarea = document.getElementById("%1$s");
  formarea.value += getquotedcontent(authorname, '');
  formarea.focus();
}

</script>