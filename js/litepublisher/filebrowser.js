  $(document).ready(function() {
    $("#tabs").tabs({
cache: true,
   load: function(event, ui) { 
//if (ui.index == 2) initfiletab();
}
});
  });

function initfiletabs() {
$.get(ltoptions.url + '/admin/ajaxposteditor.htm',
{id: ltoptions.idpost, get: "files"},
function (result) { 
$("#filetabs").html(result);
    $('#filetabs').tabs({cache: true});

$.getScript(ltoptions.files + '/js/swfupload/swfupload.js', function() {
        $.getScript(ltoptions.files + '/js/litepublisher/swfuploader.js');
});
});
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

function str_replace ( search, replace, subject ) {	
	if(!(replace instanceof Array)){
		replace=new Array(replace);
		if(search instanceof Array){//If search	is an array and replace	is a string, then this replacement string is used for every value of search
			while(search.length>replace.length){
				replace[replace.length]=replace[0];
			}
		}
	}

	if(!(search instanceof Array))search=new Array(search);
	while(search.length>replace.length){//If replace	has fewer values than search , then an empty string is used for the rest of replacement values
		replace[replace.length]='';
	}

	if(subject instanceof Array){//If subject is an array, then the search and replace is performed with every entry of subject , and the return value is an array as well.
		for(k in subject){
			subject[k]=str_replace(search,replace,subject[k]);
		}
		return subject;
	}

	for(var k=0; k<search.length; k++){
		var i = subject.indexOf(search[k]);
		while(i>-1){
			subject = subject.replace(search[k], replace[k]);
			i = subject.indexOf(search[k],i);
		}
	}

	return subject;

}

function addtocurrentfiles() {
$("input:checked[id^='itemfilepage']").each(function() {
this.checked = false;
var html =str_replace(
["itemfilepage-", "filepage-", "post-"],
["currentfile-", "curfile-", "curpost-"],
$('<div></div>').append($( this).parent().clone() ).html());
// outer html prev line
$('#currentfilestab > :first').append(html);
});
}

function delete_current_files() {
prepareform();
$("input:checked[id^='currentfile']").each(function() {
$(this).parent().remove();
 } );
}

function prepareform() {
var files = [];
$("input[id^='currentfile']").each(function() {
files.push($(this).val());
});
var s = files.join(',');
alert(s);
  return true;
}

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