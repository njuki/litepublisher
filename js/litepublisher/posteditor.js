(function( $ ){
$.posteditor = {

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
  
  
  function addtocurrentfiles() {
    $("input:checked[id^='itemfilepage']").each(function() {
      $(this).attr('checked', false);
      var id = $(this).val();
      if ($("#currentfile-" + id).length == 0) {
        var html =str_replace(
        ['pagefile-', 'pagepost-', 'itemfilepage-'],
        ['curfile-', 'curpost-', 'currentfile-'],
        $('<div></div>').append($( this).parent().clone() ).html());
        // outer html prev line
        //alert(html);
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
    var scripts = $.when(      $.load_script(ltoptions.files + '/js/swfupload/swfupload.js'),
    $.load_script(ltoptions.files + '/js/litepublisher/swfuploader.min.js'));
    
    $.get(ltoptions.url + '/admin/ajaxposteditor.htm',
  {id: ltoptions.idpost, get: "files"},
    function (html) {
      $("#filebrowser").html(html);
    $('#filetabs').tabs({cache: true});
      //$("input[id^='addfilesbutton']").live('click', addtocurrentfiles);
      $(document).on("click", "input[id^='addfilesbutton']", addtocurrentfiles);
      
      $("#deletecurrentfiles").click(function() {
        $("input:checked[id^='currentfile']").each(function() {
          $(this).parent().remove();
        } );
        return false;
      });
      
      $('form:first').submit(function() {
        $("input[name='files']").val(getpostfiles());
      });
      
      scripts.done(function() {
        ltoptions.swfu = createswfu();
      });
      
    });
  }
  
  function tagtopost() {
    var newtag  = $(this).text();
    var tags = $('#text-tags').val();
    if (tags == '') {
      $('#text-tags').val(newtag);
    } else {
      var re = /\s*,\s*/;
      var list = tags.split(re);
      for (var i = list.length; i >= 0; i--) {
        if (newtag == list[i]) return false;
      }
      $('#text-tags').val(tags + ', ' + newtag);
    }
    return false;
  },
  
  init: function() {
    $("#tabs").tabs({
      cache: true,
    select: function(event, ui) {
if ("#datetime-holder", ui.panel).length) $.posteditor.init_datetime_tab(ui.panel);
},

      load: function(event, ui) {
          $("a[rel='tagtopost']", ui.panel).click(tagtopost);
      }
    });
    
    $("a[rel~='initfiletabs']").one('click', function() {
      initfiletabs();
      return false;
    });
    
    $("a[rel~='loadcontenttabs']").one('click', function() {
      loadcontenttabs();
      return false;
    });
    
    $('form:first').submit(function() {
      if ("" == $("input[name='title']").val()) {
        error_dialog("empty title");
        return false;
      }
    });
    
  },

  init_datetime_tab: function (uipanel) {
//replace html in comment
var holder = $("#datetime-holder", uipanel);
holder.replaceWith(holder.get(0).firstChild.nodeValue);
this.load_ui_datepicker(function() {
    var cur = $("#text-date").val();
    $("#datepicker").datepicker({
      altField: "#text-date",
      altFormat: "dd.mm.yy",
      dateFormat: "dd.mm.yy",
      changeYear: true
      //showButtonPanel: true
    });
    
    $("#datepicker").datepicker("setDate", cur);
});
  },
  
load_ui_datepicker: function(callback) {
          $.load_script(ltoptions.files + '/js/jquery/ui-' + $.ui.version + '/jquery.ui.datepicker.min.js', function() {
            if (ltoptions.lang == 'en') return callback();
              $.load_script(ltoptions.files + '/js/jquery/ui-' + $.ui.version + '/jquery.ui.datepicker-' + ltoptions.lang + '.js', callback);
          });
}

}
})( jQuery );