/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var commentclient;

function createcommentclient() {
  return new rpc.ServiceProxy(ltoptions.pingback, {
    asynchronous: true,
    protocol: 'XML-RPC',
    sanitize: false,
    methods: [
    'litepublisher.moderate',
    'litepublisher.deletecomment',
    'litepublisher.comments.setstatus',
    'litepublisher.comments.add',
    'litepublisher.comments.get',
    'litepublisher.comments.edit',
    'litepublisher.comments.reply',
    'litepublisher.comments.getrecent'
    ]
    //callbackParamName: 'callback'
  });
}


function movecomment(id, status) {
  var item =document.getElementById("comment-" + id);
  var idnewparent = ltoptions.commentsid;
  if (status == 'hold') idnewparent = 'hold' + ltoptions.commentsid;
  var newparent = document.getElementById(idnewparent);
  newparent.appendChild(item.cloneNode(true));
  item.parentNode.removeChild(item);
}

function deletecomment(id) {
  if (!confirm(lang.comments.confirmdelete)) return;
  if (commentclient == undefined) commentclient = createcommentclient();
  commentclient.litepublisher.deletecomment( {
    
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
  if (commentclient == undefined) commentclient = createcommentclient();
  commentclient.litepublisher.comments.setstatus( {
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
    if (!confirm(lang.comments.confirmdelete)) return;
  }
  
  if (commentclient == undefined) commentclient = createcommentclient();
  commentclient.litepublisher.moderate( {
    params:['', '', ltoptions.idpost, list, action],
    
    onSuccess:function(result){
      for (var i = 0, n = list.length; i <n; i++) {
        if (action == 'delete') {
          var item =document.getElementById("comment-" + list[i]);
          item.parentNode.removeChild(item);
        } else {
          movecomment(list[i], action);
        }
      }
    },
    
    onException:function(errorObj){
      alert(lang.comments.notmoderated);
    },
    
  onComplete:function(responseObj){ }
  } );
  
}

function submitmoderateform(form, action) {
  var list = new Array();
  for (var i = 0, n = form.elements.length; i < n; i++) {
    var elem = form.elements[i];
    if((elem.type == 'checkbox') && (elem.checked == true)) {
      list.push(parseInt(elem.value));
    }
  }
  moderate(list, action);
}

function editcomment(id) {
  if (commentclient == undefined) commentclient = createcommentclient();
  commentclient.litepublisher.comments.get( {
    params:['', '', id, ltoptions.idpost],
    
    onSuccess:function(result){
      try {
        document.getElementById('name').value = result.name;
        document.getElementById('email').value = result.email;
        document.getElementById('url').value = result.url;
        document.getElementById('comment').value = result.rawcontent;
        
        document.getElementById('commentform').onsubmit = submiteditcomment(id);
        document.getElementById('name').focus();
      } catch (e) {
        alert(e.message);
      }
    },
    
    onException:function(errorObj){
      alert(lang.comments.errorrecieved);
    },
    
  onComplete:function(responseObj){ }
  } );
}

function submiteditcomment(id) {
  return function() {
    commentclient.litepublisher.comments.edit( {
      params:['', '', id, ltoptions.idpost, {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        url: document.getElementById('url').value,
        content: document.getElementById('comment').value
      }],
      
      onSuccess:function(result){
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('url').value = '';
        var content = document.getElementById('comment').value;
        document.getElementById('comment').value = '';
        document.getElementById('commentform').onsubmit = null;
        
        var p = document.getElementById('commentcontent-' + id);
        p.innerHTML = content;
        document.getElementById('checkbox-comment-' + id).focus();
      },
      
      onException:function(errorObj){
        alert(lang.comments.notedited);
      },
      
    onComplete:function(responseObj){ }
    } );
    
    return false;
  };
}

function sendreply() {
  try {
    var content =         document.getElementById('comment').value;
    if (content == '') {
      alert(lang.comment.emptycontent);
      return;
    }
    
    if (commentclient == undefined) commentclient = createcommentclient();
    commentclient.litepublisher.comments.reply( {
      params:['', '', 0, ltoptions.idpost, content],
      
      onSuccess:function(result){
        try {
          document.getElementById('comment').value = '';
          window.location .reload(true);
        } catch (e) {
          alert(e.message);
        }
        
      },
      
      onException:function(errorObj){
        alert(lang.comments.notedited);
      },
      
    onComplete:function(responseObj){ }
    } );
    
  } catch (e) {
    alert(e.message);
  }
  
}