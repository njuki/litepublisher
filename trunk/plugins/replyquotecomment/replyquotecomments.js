<script type="text/javascript">
function quotecomment(id, authorname) {
  var formarea = document.getElementById("%1$s");
  var cmtcontent = document.getElementById('co_' + id);

  if (window.getSelection) { var sel = window.getSelection(); }
  else if (document.getSelection) { var sel = document.getSelection(); }
  else if (document.selection) { var sel = document.selection.createRange().text; }

  if (cmtcontent.innerText){
	  if (sel != "") formarea.value += "<b>" + authorname + "</b> wrote:\n<blockquote>" + sel + "</blockquote>\n"; 
		else formarea.value += "<b>" + authorname + "</b> wrote:\n<blockquote>" + cmtcontent.innerText + "</blockquote>\n";
  } else { 
	  if (sel != "") formarea.value += "<b>" + authorname + "</b> wrote:\n<blockquote>" + sel + "</blockquote>\n"; 
		else formarea.value += "<b>" + authorname + "</b> wrote:\n<blockquote>" + cmtcontent.textContent + "</blockquote>\n";
  }

  formarea.focus();
}

function replycomment(id, authorname) {
  var formarea = document.getElementById("%1$s");
  formarea.value += "<b>@ " + authorname + "</b>:\n";
  formarea.focus();
}

</script> 	
