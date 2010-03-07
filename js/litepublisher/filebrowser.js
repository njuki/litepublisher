/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

//ansync load javascripts
var swfumutex = {
  creator: false,
  uploader: false
};

function filebrowser(link) {
  if (client == undefined) client = createclient();
  client.litepublisher.files.getbrowser( {
    params:['', '', ltoptions.idpost],
    
    onSuccess:function(result){
      try {
        var div = document.createElement("div");
        div.innerHTML = result;
        var browser = document.getElementById("filebrowser");
        browser.parentNode.replaceChild(div, browser);
        widgets.add(link, div);
        var form = document.getElementById("form");
        form.onsubmit = submitform;
        var hidden = document.createElement('input');
        hidden.type= 'hidden';
        hidden.name = 'fileschanged';
        hidden.value = '1';
        form.appendChild(hidden);
        
        ltoptions.idfilepages = "filepages";
        ltoptions.idfilepage = "filepage";
        ltoptions.idcurrentfiles = "currentfiles";
        
        //createswfu();
        loadjavascript('/js/swfupload/swfupload.js');
        loadjavascript('/js/litepublisher/swfuploader.js');
      } catch (e) {
        alert('Error ' + e.message);
      }
    },
    
    onException:function(errorObj){
      alert("Server error");
    },
    
  onComplete:function(responseObj){ }
  } );
  
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
  if (client == undefined) client = createclient();
  client.litepublisher.files.getpage( {
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
  if (client == undefined) client = createclient();
  client.litepublisher.files.geticons( {
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
  if (client == undefined) client = createclient();
  client.litepublisher.files.getthemes( {
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
  if (client == undefined) client = createclient();
  client.litepublisher.files.getthemes( {
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
  
  if (client == undefined) client = createclient();
  client.litepublisher.files.gettags( {
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

function tagclicked(link) {
  try {
    var newtag  = link.innerHTML ;
    var edit = document.getElementById("tags");
    var tags = edit.value;
    if (tags == '') {
      edit.value = newtag;
      return;
    }
    
    var re = /\s*,\s*/;
    var list = tags.split(re);
    for (var i = list.length; i >= 0; i--) {
      if (newtag == list[i]) return;
    }
    
    edit.value += ', ' + newtag;
    
} catch(e) { alert(e.message); }
}