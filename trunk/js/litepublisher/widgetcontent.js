var client;

function createclient() {
return new rpc.ServiceProxy(ltoptions.pingback, {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.getwidget',
'litepublisher.moderate',
'litepublisher.comments.delete', 
'litepublisher.comments.setstatus',
'litepublisher.comments.add',
'litepublisher.comments.get',
'litepublisher.comments.edit',
'litepublisher.comments.getrecent',
'litepublisher.files.getbrowser',
'litepublisher.files.getpage'
]
//callbackParamName: 'callback'
}); 
}

	function loadwidget(name, idtag) {
		var widget = document.getElementById(idtag);
if (!client) client = createclient();

client.litepublisher.getwidget( {
params:[name],

                 onSuccess:function(result){                     
if (result) {
widget.innerHTML = result;
} else {
                    //alert('problem');
}
},

                  onException:function(errorObj){ 
//                    alert('error'.notdeleted);
},

onComplete:function(responseObj){ }
} );

}
