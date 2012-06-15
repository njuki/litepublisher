(function( $ ){
$.fileman = {
items: [],

init: function(holder) {
this.init_templates();
$(holder).html(this.templates.tab);
    var tabs = $("#posteditor-files-tabs");
tabs.tabs({
cache: true,
    select: function(event, ui) {
if ("empty" == $(ui.panel).data("files")) {
$.fileman.loadpage(ui.panel, $(ui.panel).data("page"));
}
}
});

$.litejson({method: "files_get", idpost: ltoptions.idpost}, function (r) {
$.fileman.set_tabs_count(r.count);
$.fileman.joinitems(r.files);
$fileman.setpage("#filepage-0", r.files);
});

        ltoptions.swfu = createswfu($.fileman.uploaded);      

      $('form:first').submit(function() {
        $("input[name='files']").val(getpostfiles());
      });
  },

init_templates: function() {
lang.posteditor.iconurl =ltoptions.files + "/js/litepublisher/icons/";
for (var prop in this.templates) {
this.templates[prop] = Mustache.render(this.templates[prop], lang.posteditor);
}
},

set_tabs_count: function(count) {
if (count < 1) count = 1;
var tabs = $("#posteditor-files-tabs");
for (var i =1; i <= count; i++) {
$('<div id="filepage-' + i + '"></div>').appendTo(tabs).data("page", i).data("files", "empty");
tabs.tabs( "add" , "#filepage-" + i, i);
},

setpage: function(uipanel, files) {
var panel =$(uipanel);
for (var id in files) {
if (parseInt(fileitem['parent']) != 0) continue;
$(this.get_fileitem(id)).appendTo(panel).data("idfile", id);
}

panel.on("click", ".toolbar a", function() {
var idfile = $(this).closest(".file-item").data("idfile");
switch($(this).class()) {
case "add-toolbutton":
$.fileman.add(idfile);
break;

case "delete-toolbutton":
$.fileman.del(idfile);
break;

case "property-toolbutton":
$.fileman.editprops(idfile);
break;
}

return false;
});
},

get_fileitem: function(id) {
var item =this.files[id];
type = (item["type"] in this.templates) ? item["type"] : "file";
if (parseInt(item["preview"]) != 0) item["img"] = Mustache.render(this.templates["preview"], this.files[item["preview"]]);
return Mustache.render(this.templates.item, {
id: item["id"],
content: Mustache.render(this.templates[type], item)
});
},

loadpage: function(uipanel, page) {
$(uipanel).data("files", "loading");
$.litejson({method: "files_getpage", page: page - 1}, function(r) {
$.fileman.joinitems(r.files);
$.fileman.setpage(uipanel, r.files);
});
},

joinitems: function(files) {
for (var id in files) {
this.items[id] = files[id];
}
},

uploaded: function(file, serverData) {
  var haschilds = $("#newfilestab").children().length > 0;
  $("#newfilestab").append(serverData);
  var html = $("#newfilestab").children(":last").html();
  if (haschilds) {
    $("#newfilestab").children(":last").remove();
    $("#newfilestab").children(":first").append(html);
  }
  html =str_replace(
  ['uploaded-', 'new-post-', 'newfile-'],
  ['curfile-', 'curpost-', 'currentfile-'],
  html);
  $('#currentfilestab > :first').append(html);
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
  



};
})( jQuery );