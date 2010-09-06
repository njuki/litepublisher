var youtube = {
url: '',
token: '',
inrequest: false,
  client : new rpc.ServiceProxy(ltoptions.pingback, {
    asynchronous: true,
    protocol: 'XML-RPC',
    sanitize: false,
    methods: ['litepublisher.youtube.getuploadtoken']
  })
};

youtube.submitform =  function() {
if (youtube.inrequest) return youtube.print('Send request');
    if (document.getElementById('file').value == '') return youtube.print('You must select file to upload');

if (youtube.token == '') {
youtube.inrequest = true;
youtube.client.litepublisher.youtube.getuploadtoken({
    params:['', '',
        document.getElementById('title').value,
        document.getElementById('description').value,
         document.getElementById('category').value,
         document.getElementById('keywords').value
],

    onSuccess:function(result){
      if (result && (result != 'false')) {
youtube.url = result.url;
youtube.token = result.token;
        document.getElementById('token').value = result.token;
        var form = document.getElementById('youtubeuploadform');
form.action = result.url;
form.onsubmit = null;
form.submit();
} else {
alert('Error token');
}
},

    onException:function(errorObj){
      alert("Server error");
    },
    
  onComplete:function(responseObj){ }
  } );

return false;
};

youtube.print = function (s) {
document.getElementById('infostatus').innerHTML = s;
return false;
};