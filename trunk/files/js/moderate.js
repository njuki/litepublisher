function createmoderator() {
return new rpc.ServiceProxy(ltoptions.xmlrpc, {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.moderate',
'litepublisher.deletecomment', 
'litepublisher.setcommentstatus',
'litepublisher.addcomment',
'litepublisher.getcomment',
'litepublisher.getrecentcomments'
],
//callbackParamName: 'callback'
}); 
}

function singlemoderate(id, action) {
if (action == 'delete') {
if (!confirm("Do you realy want to delete comment?")) return;
}

var item =document.getElementById("comment-" + id);
var client = createmoderator();
if (action == 'delete') {
client.litepublisher.deletecomment( {
params:['', '', id, ltoptions.idpost],

                 onSuccess:function(result){                     
if (result) {
    item.parentNode.removeChild(item);
} else {
                    alert(ltoptions.lang.commentnotdeleted);
}
},

                  onException:function(errorObj){ 
                    alert(ltoptions.lang.commentnotdeleted);
},

onComplete:function(responseObj){ }
} );
} else {
client.litepublisher.setcommentstatus( {
params:['', '', id, ltoptions.idpost, action],

                 onSuccess:function(result){                     
if (result) {
    item.parentNode.removeChild(item);
//добавитьэтот коммент в нужный список, а списки надо разрулить в теме, а id списков добавить в передаваемые опции
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

}

function moderate(list, action) {
if (action == 'delete') {
if (!confirm("Do you realy want to delete comment?")) return;
}

var client = createmoderator();
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
var list [];
	for (var i = 0, n = form.elements.length; i < n; i++) {
var elem = form.elements[i];
		if((elem.type == 'checkbox') && (elem.checked == true)) {
list.push(parseint(elem.value));
		}
	}

moderate(list, action);
}