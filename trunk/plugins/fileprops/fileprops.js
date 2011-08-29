function fileprops_dialog(props) {

}


$(document).ready(function() {
$("#filetabs a[rel^='prettyPhoto']").live('click', function() {
var props = $(this).data"fileprops");
if (props) {
fileprops_dialog(props);
} else {
var link = $(this);
  $.get(ltoptions.url + '/admin/fileprops.htm', {filename: $(this).attr("href")}, function (fileprops) {
fileprops.link = link;
link.data("fileprops", fileprops);
fileprops_dialog(fileprops);
});
}
return false;
});
});

