/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function initdatepicker() {
  var cur = $("#date").val();
  $('#datepicker').datepicker({
    altField: '#date',
    altFormat: 'dd.mm.yy',
    dateFormat: 'dd.mm.yy',
    changeYear: true
    //showButtonPanel: true
  });
  
  $("#datepicker").datepicker("setDate", cur);
}

function loadcontenttabs() {
  $("#loadcontenttabs").remove();
  $.get(ltoptions.url + '/admin/ajaxposteditor.htm',
{id: ltoptions.idpost, get: "contenttabs"},
  function (html) {
    $(html).insertBefore("#raweditor");
    $("#raweditor").appendTo("#rawtab");
  $('#contenttabs').tabs({cache: true});
  });
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

var addtocurrentfiles= function() {
  $("input:checked[id^='itemfilepage']").each(function() {
    $(this).attr('checked', false);
    var id = $(this).val();
    if ($("#currentfile-" + id).length == 0) {
      var html =str_replace(
      ['pagefile-', 'pagepost-', 'itemfilepage-'],
      ['curfile-', 'curpost-', 'currentfile-'],
      $('<div></div>').append($( this).parent().clone() ).html());
      // outer html prev line
      $('#currentfilestab > :first').append(html);
    }
  });
}

function getpostfiles() {
  var files = [];
  $("input[id^='currentfile']").each(function() {
    files.push($(this).val());
  });
  return files.join(',');
}

function initfiletabs() {
  $.get(ltoptions.url + '/admin/ajaxposteditor.htm',
{id: ltoptions.idpost, get: "files"},
  function (html) {
    $("#filebrowser").html(html);
  $('#filetabs').tabs({cache: true});
  $("input[id^='addfilesbutton']").live('click', addtocurrentfiles);

  $("#deletecurrentfiles").click(function() {
  $("input:checked[id^='currentfile']").each(function() {
    $(this).parent().remove();
  } );
});

 $('form:first').submit(function() {
      $("#files").val(getpostfiles());
    });
    
    $.getScript(ltoptions.files + '/js/swfupload/swfupload.js', function() {
      $.getScript(ltoptions.files + '/js/litepublisher/swfuploader.js');
    });
  });
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

$(document).ready(function() {
          $.getScript(ltoptions.files + '/files/admin' + ltoptions.lang + '.js');
inittabs("#tabs", function() {

  $("#tabs").bind( "tabsload", function(event, ui) {
      switch (ui.index) {
        case 2:
          $.getScript(ltoptions.files + '/js/jquery/ui/jquery.ui.datepicker.min.js', function() {
          initdatepicker();
        if (ltoptions.lang == 'en') {
} else {
          $.getScript(ltoptions.files + '/js/jquery/ui/jquery.ui.datepicker-' + ltoptions.lang + '.js', function() {
          initdatepicker();
});
}
});
        break;
      }
  });

  $("a[rel~='initfiletabs']").click(function() {
    $(this).unbind('click');
initfiletabs();
return false;
});

  $("a[rel~='loadcontenttabs']").click(function() {
    $(this).unbind('click');
loadcontenttabs();
return false;
});

  $("a[rel~='loadvisual']").click(function() {
    $(this).unbind('click');
  $("#loadvisual").remove();
  $.getScript(ltoptions.files + ltoptions.visual);
return false;
});

});
});
