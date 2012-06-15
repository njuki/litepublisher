(function( $ ){
$.fileman = {
items: {},
curr: [],
indialog: false,

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
for (var id in r.files) {
$.fileman.curr.push(id);
$.fileman.items[id] = r.files[id];
}

$fileman.setpage("#current-files", r.files);
//to assign events
$fileman.setpage("#new-files", []);
})
          .fail( function(jq, textStatus, errorThrown) {
$.messagebox(lang.dialog.error, jq.responseText);
});


        ltoptions.swfu = createswfu($.fileman.uploaded);      

      $('form:first').submit(function() {
        $("input[name='files']").val($.fileman.curr.join(','));
      });
  },

init_templates: function() {
lang.posteditor.iconurl =ltoptions.files + "/js/litepublisher/icons/";
for (var prop in this.templates) {
this.templates[prop] = Mustache.render(this.templates[prop], lang.posteditor);
}
},

set_tabs_count: function(count) {
if (count < 1) return;
var tabs = $("#posteditor-files-tabs");
for (var i =1; i <= count; i++) {
$(this.templates.tab.replace('{{index}}', i)).appendTo(tabs).data("page", i).data("files", "empty");
tabs.tabs( "add" , "#filepage-" + i, i);
}
},

setpage: function(uipanel, files) {
var panel =$(uipanel);
for (var id in files) {
if (parseInt(fileitem['parent']) != 0) continue;
panel.append(this.get_fileitem(id));
}

panel.on("click", ".toolbar a", function() {
var holder = $(this).closest(".file-item")
var idfile = holder.data("idfile");

switch(
$(this).attr("class")) {
case "add-toolbutton":
$.fileman.add(idfile);
break;

case "delete-toolbutton":
$.fileman.del(idfile, holder);
break;

case "property-toolbutton":
$.fileman.editprops(idfile, holder);
break;
}

return false;
});
},

get_fileitem: function(id) {
var item =this.files[id];
item.link = ltoptions.files + "/files/" + item.url;
type = (item["type"] in this.templates) ? item["type"] : "file";
if (parseInt(item["preview"]) != 0) item.previewlink = ltoptions.files + "/files/" + this.files[item["preview"]]["url"];
var html = Mustache.render(this.templates.item, {
id: item["id"],
content: Mustache.render(this.templates[type], item)
});

return $(html).data("idfile", idfile);
},

loadpage: function(uipanel, page) {
$(uipanel).data("files", "loading");
$.litejson({method: "files_getpage", page: page - 1}, function(r) {
$.fileman.joinitems(r.files);
$.fileman.setpage(uipanel, r.files);
})
          .fail( function(jq, textStatus, errorThrown) {
$.messagebox(lang.dialog.error, jq.responseText);
});
},

joinitems: function(files) {
for (var id in files) {
this.items[id] = files[id];
}
},

uploaded: function(file, serverData) {
var r = $.parseJSON(serverData);
/* r = {
id: int idfile,
item: array fileitem,
preview: array fileitem optimal
}*/

var idfile = r.id;
$.fileman.curr.push(idfile);
$.fileman.items[idfile] = r.item;
if (r.item["preview"] != 0) $.fileman.items[r.preview['id']] = r.preview;

$("#current-files").append($.fileman.get_fileitem(idfile));
$("#new-files").append($.fileman.get_fileitem(idfile));
},

add: function(idfile) {
      if ($.inArray(idfile, this.curr) >= 0) return;
this.curr.push(idfile);

$("#current-files").append(this.get_fileitem(idfile));
},

del: function(idfile, holder) {
var i = $.inArray(idfile, this.curr);
if (i < 0) return;
delete this.curr[i];
holder.remove();
},

editprops: function(idfile, owner) {
if (this.indialog) return false;
this.indialog = true;
var fileitem = this.items[idfile];

$.prettyPhotoDialog({
title: lang.posteditor.property,
html: this.templates.fileprops,
open: function(holder) {
$("input[name='fileprop-title']", holder).val(fileitem.title);
$("input[name='fileprop-description']", holder).val(fileitem.description);
$("input[name='fileprop-keywords']", holder).val(fileitem.keywords);
},

buttons: [
{
        title: "Ok",
        click: function() {
var holder = $(".pp_inline");
var title = $.trim($("input[name='fileprop-title']", holder).val());
var description = $.trim($("input[name='fileprop-description']", holder).val());
var keywords = $.trim($("input[name='fileprop-keywords']", holder).val());
          $.prettyPhoto.close();
$.fileman.setprops(idfile, title, description, keywords, owner);
}
    },
{
        title: lang.dialog.cancel,
        click: function() {
          $.prettyPhoto.close();
$.fileman.indialog = false;
}
    }
]
} );
},

setprops: function(idfile, title, description, keywords, holder) {
$.litejsontype("post",{method: "files_setprops", idfile: idfile, title: title, description: description, keywords: keywords}, function(r) {
$.fileman.items[r.item["id"]] = r.item;
//need to update infos but we cant find all files
holder.replaceWith($.fileman.get_fileitem(idfile));
$.fileman.indialog = false;
})
          .fail( function(jq, textStatus, errorThrown) {
$.fileman.indialog = false;
$.messagebox(lang.dialog.error, jq.responseText);
});
}

};//fileman
})( jQuery );