<script type="text/javascript">
function moderate(id, idpost, action) {
if (action == 'delete') {
if (!if !confirm("Do you realy want to delete comment?")) return;
}
var item =document.getElementById("comment-" + id);

var link = "%s/admin/ajax/comments/%sid=" + id + "&idpost=" + idpost + "&action=" + action;
		var http = createRequestObject();				
		if( http ) {
			http.open('get', link);
			http.onreadystatechange = function () {
				if((http.readyState == 4) && (http.status == '200') && (http.responseText == 'ok')) {
 switch(action) {
case 'delete':
    item.parentNode.removeChild(item);
break;

case 'hold':
break;

case 'approve':
break;
}
				}
			}
			http.send(null);    
		}
	}

</script>