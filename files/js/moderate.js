function movecomment(id, status) {
var item =document.getElementById("comment-" + id);
item.parentNode.removeChild(item);
//var idparent = parent.attributes.getNamedItem('id').nodeValue;
var idnewparent = ltoptions.commentsid;
if (status == 'hold') idnewparent = 'hold' + ltoptions.commentsid;
var newparent = document.getElementById(idnewparent);
newparent.appendChild(item);
}

function deletecomment(id) {
if (!confirm(lang.comments.confirmdelete)) return;
if (client == undefined) client = createclient();
client.litepublisher.deletecomment( {
params:['', '', id, ltoptions.idpost],

                 onSuccess:function(result){                     
var item =document.getElementById("comment-" + id);
    item.parentNode.removeChild(item);
},

                  onException:function(errorObj){ 
                    alert(lang.comments.notdeleted);
},

onComplete:function(responseObj){ }
} );
}

function setcommentstatus(id, status) {
if (client == undefined) client = createclient();
client.litepublisher.setcommentstatus( {
params:['', '', id, ltoptions.idpost, status],

                 onSuccess:function(result){                     
alert('sucess');
//movecomment(id, status);
},

                  onException:function(errorObj){ 
                    alert(lang.comments.notmoderated);
},

onComplete:function(responseObj){ }
} );
}

function moderate(list, action) {
if (action == 'delete') {
if (!confirm("Do you realy want to delete comment?")) return;
}

if (client == undefined) client = createclient();
client.litepublisher.moderate( {
params:['', '', ltoptions.idpost, list, action],

                 onSuccess:function(result){                     
if (result) {
for (var i = 0, n = list.length; i <n; i++) {
var id = list[i];
var item =document.getElementById("comment-" + id);
//или переместить
    item.parentNode.removeChild(item);
}
} else {
                    alert(ltoptions.lang.commentnotmoderated);
}
},

                  onException:function(errorObj){ 
                    alert(ltoptions.lang.commentnotmoderated);
},

onComplete:function(responseObj){ }
} );

}

function submitmoderateform(form, action) {
var list = new array();
	for (i = 0, n = form.elements.length; i < n; i++) {
var elem = form.elements[i];
		if((elem.type == 'checkbox') && (elem.checked == true)) {
list.push(parseint(elem.value));
		}
	}

moderate(list, action);
}