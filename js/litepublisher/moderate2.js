(function( $ ){
  $.fn.moderatebuttons= function() {
    return this.click(function() {
var self = $(this);
var action = self.data("moderate");
var id = self.parent().data("idcomment");
$.moderate_comment(id, action);
return false;
});
};

$.move_comment = function(id, status) {
  var item =$("#comment-" + id);
  var parent = $("#" + (status == "hold" ? "hold" : "") + ltoptions.commentsid);
if (item.parent() != parent) parent.append(item);
};

$.moderate_comment = function (id, status) {
var idcomment = "#comment-" + id;
switch (status) {
case "delete":
  if (!confirm(lang.comments.confirmdelete)) return;
$.litejson("comment_delete", {id: id}, function(r){
$(idcomment).remove();
    })
    .error( function(jq, textStatus, errorThrown) {
      //alert('error ' + jq.responseText );
      alert(lang.comments.notdeleted);
    };
}
break;

case "hold":
case "approved":
$.litejson("comment_setstatus", {id: id, status: status}, function(r) {
      $.move_comment(id, status);
    })
        .error( function(jq, textStatus, errorThrown) {
      alert(lang.comments.notmoderated);
    });
break;
    
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


})( jQuery );
