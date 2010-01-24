function movecomment(id, status) {
var item =document.getElementById("comment-" + id);
//var idparent = parent.attributes.getNamedItem('id').nodeValue;
var idnewparent = ltoptions.commentsid;
if (status == 'hold') idnewparent = 'hold' + ltoptions.commentsid;
var newparent = document.getElementById(idnewparent);
newparent.appendChild(item.cloneNode(true));
item.parentNode.removeChild(item);
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
movecomment(id, status);
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
for (var i = 0, n = list.length; i <n; i++) {
if (action == 'delete') {
var item =document.getElementById("comment-" + list[i]);
    item.parentNode.removeChild(item);
} else {
movecomment(list[id], action);
}
}
},

                  onException:function(errorObj){ 
                    alert(ltoptions.lang.commentnotmoderated);
},

onComplete:function(responseObj){ }
} );

}

function submitmoderateform(form, action) {
var list = new Array();
	for (var i = 0, n = form.elements.length; i < n; i++) {
var elem = form.elements[i];
		if((elem.type == 'checkbox') && (elem.checked == true)) {
list.push(parseint(elem.value));
		}
	}

moderate(list, action);
}

function editcomment(id) {
if (client == undefined) client = createclient();
client.litepublisher.getcomment( {
params:['', '', id, ltoptions.idpost],

                 onSuccess:function(result){                     
document.getElementById('name').value = result.name;
document.getElementById('email').value = result.email;
document.getElementById('url').value = result.url;
document.getElementById('comment').value = result.content;

document.getElementById('commentform').onsubmit = submiteditcomment(id);
},

                  onException:function(errorObj){ 
                    alert('err');
},

onComplete:function(responseObj){ }
} );
}

function submiteditcomment(id) {
client.litepublisher.editcomment( {
params:['', '', id, ltoptions.idpost, {
name: document.getElementById('name').value,
email: document.getElementById('email').value,
url: document.getElementById('url').value,
content: document.getElementById('comment').value
}],

                 onSuccess:function(result){                     
alert('edited');
},

                  onException:function(errorObj){ 
                    alert('err');
},

onComplete:function(responseObj){ }
} );//litepublisher.editcomment
return false;
}