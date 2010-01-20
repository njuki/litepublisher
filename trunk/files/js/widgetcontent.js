function createmoderator() {
return new rpc.ServiceProxy(
'http://fireflyblog.ru/rpc.xml', {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.getwidget',
'litepublisher.moderate',
'litepublisher.deletecomment', 
'litepublisher.setcommentstatus',
'litepublisher.addcomment',
'litepublisher.getcomment',
'litepublisher.getrecentcomments'
]
//callbackParamName: 'callback'
}); 
}

	function loadwidget(name, idtag) {
		var widget = document.getElementById(idtag);
var client = createmoderator();

client.litepublisher.getwidget( {
params:[name],

                 onSuccess:function(result){                     
if (result) {
widget.innerHTML = result;
} else {
                    alert(lang.comments.notdeleted);
}
},

                  onException:function(errorObj){ 
                    alert('error'.notdeleted);
},

onComplete:function(responseObj){ }
} );

}
