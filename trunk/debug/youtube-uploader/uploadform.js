var youtube = {
url: '',
token: ''
};

youtube.init =   function() {
  this.client = new rpc.ServiceProxy(ltoptions.pingback, {
    asynchronous: true,
    protocol: 'XML-RPC',
    sanitize: false,
    methods: ['litepublisher.youtube.getuploadtoken']
  });

var form = document.getElementById('youtubeuploadform');
form.onsubmit = this.submitform;
};

youtube.submitform =  function() {
if (youtube.token == '') {
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
} else {
}
};

//youtube.init();

  function checkForFile() { 
    if (document.getElementById('file').value) { 
      return true; 
    } 
    document.getElementById('errMsg').style.display = ''; 
    return false; 
  } 
