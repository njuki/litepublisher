  $(document).ready(function() {
    $("#tabs").tabs({
cache: true,
   load: function(event, ui) { 
if (ui.index == 2) initfiletab();
}
});
  });

function initfiletab() {
$.getScript(ltoptions.files + '/js/swfupload/swfupload.js', function() {
        $.getScript(ltoptions.files + '/js/litepublisher/swfuploader.js');
});

      try {
  $(document).ready(function() {
    $('#filetabs').tabs({cache: true});
  });

        ltoptions.idfilepages = "filepages";
        ltoptions.idfilepage = "filepage";
        ltoptions.idcurrentfiles = "currentfiles";
        
      } catch (e) {  alert('Error ' + e.message); }
}

function getcookie(name) {
  var cookie = " " + document.cookie;
  var search = " " + name + "=";
  var setStr = null;
  var offset = 0;
  var end = 0;
  if (cookie.length > 0) {
    offset = cookie.indexOf(search);
    if (offset != -1) {
      offset += search.length;
      end = cookie.indexOf(";", offset)
      if (end == -1) {
        end = cookie.length;
      }
      setStr = unescape(cookie.substring(offset, end));
    }
  }
  return(setStr);
}

var post = {
  id: ltoptions.idpost
};

post.add= function(html) {
  document.getElementById(ltoptions.idcurrentfiles).innerHTML += html;
}

post.addfrompage = function() {
  var elems = document.getElementById(ltoptions.idfilepage).getElementsByTagName("input");
  for (var i =0, n = elems.length; i < n; i++) {
    if((elems[i].type == 'checkbox') && (elems[i].checked == true)) {
      elems[i].checked = false;
      var id = elems[i].value;
      if (elem = document.getElementById("fileitem-curr-" + id)) continue;
      var elem = document.getElementById("fileitem-pages-" + id);
      var li = elem.cloneNode(true);
      li.id = "fileitem-curr-" + id;
      var check = li.getElementsByTagName("input")[0];
      check.id = "filecheckbox-" + id;
      document.getElementById(ltoptions.idcurrentfiles).appendChild(li);
    }
  }
}

post.deletechecked = function() {
  var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
  for (var i =0, n = elems.length; i < n; i++) {
    if((elems[i].type == 'checkbox') && (elems[i].checked == true)) {
      var id = elems[i].value;
      var elem = document.getElementById("fileitem-curr-" + id);
      elem.parentNode.removeChild(elem);
    }
  }
}

post.getpage = function (page) {
  if (fileclient == undefined) fileclient = createfileclient();
  fileclient.litepublisher.files.getpage( {
    params:['','', page],
    
    onSuccess:function(result){
      var div = document.getElementById(ltoptions.idfilepages);
      div.innerHTML  = result;
    },
    
    onException:function(errorObj){
      alert("Server error");
    },
    
  onComplete:function(responseObj){ }
  } );
  
}

var submitform = function() {
  //disable delete button
  document.getElementById("deletechecked").disabled = "disabled";
  var elems = document.getElementById(ltoptions.idcurrentfiles).getElementsByTagName("input");
  for (var i =0, n = elems.length; i < n; i++) {
    if(elems[i].type == 'checkbox') {
      elems[i].checked = true;
    }
  }
  
  var elems = document.getElementById(ltoptions.idfilepage).getElementsByTagName("input");
  for (var i =0, n = elems.length; i < n; i++) {
    if(elems[i].type == 'checkbox') {
      elems[i].checked = false;
    }
  }
  
  return true;
};

function iconbrowser(link, idicon) {
  var span = document.getElementById("iconbrowser");
  if (!span) return alert('Tag not found');
  widgets.add(link, span);
  if (fileclient == undefined) fileclient = createfileclient();
  fileclient.litepublisher.files.geticons( {
    params:['', '', idicon],
    
    onSuccess:function(result){
      span.innerHTML  = result;
    },
    
    onException:function(errorObj){
      alert("XMLRPC server error");
    },
    
  onComplete:function(responseObj){ }
  } );
}

function themebrowser(link, themename) {
  var div = document.getElementById("themebrowser");
  widgets.add(link, div);
  if (fileclient == undefined) fileclient = createfileclient();
  fileclient.litepublisher.files.getthemes( {
    params:['', '', themename],
    
    onSuccess:function(result){
      div.innerHTML  += result;
    },
    
    onException:function(errorObj){
      alert("XMLRPC server error");
    },
    
  onComplete:function(responseObj){ }
  } );
  
}

function selecttheme(link, themename, name) {
  var div = document.getElementById(name);
  widgets.add(link, div);
  if (fileclient == undefined) fileclient = createfileclient();
  fileclient.litepublisher.files.getthemes( {
    params:['', '', themename],
    
    onSuccess:function(result){
      result = result.replace(new RegExp('"theme"','g'), '"' + name + '"');
      div.innerHTML  += result;
    },
    
    onException:function(errorObj){
      alert("XMLRPC server error");
    },
    
  onComplete:function(responseObj){ }
  } );
  
}

function tagsbrowser(link) {
  var editparent = document.getElementById("tags").parentNode;
  if (!editparent) return alert('Parent Edit not found');
  var p = document.createElement("p");
  editparent.parentNode.insertBefore(p, editparent.nextSibling);
  widgets.add(link, p);
  
  if (fileclient == undefined) fileclient = createfileclient();
  fileclient.litepublisher.files.gettags( {
    params:['', ''],
    
    onSuccess:function(result){
      p.innerHTML  = result;
    },
    
    onException:function(errorObj){
      alert("XMLRPC server error");
    },
    
  onComplete:function(responseObj){ }
  } );
}

function tagtopost(link) {
    var newtag  = $(link).html();
    var tags = $('#tags').val();
    if (tags == '') {
$('#tags').val(newtag);
    } else {
        var re = /\s*,\s*/;
    var list = tags.split(re);
    for (var i = list.length; i >= 0; i--) {
      if (newtag == list[i]) return;
    }
$('#tags').val(tags + ', ' + newtag);
    }
}
// catch(e) { alert(e.message); 