  $(document).ready(function() {
initftpbrowser();
});

function initftpbrowser() {
$("#ftpbrowser").find("a").click(function() {
openftpfolder($(this).value());
});
}

function openftpfolder(foldername) {
$.post(ltoptions.url + "/admin/ajaxftpbrowser.htm",
{
host: $("#host").val(),
login: $("#login").val(),
password:  $("#password").val(),
folder: foldername
},
function (html) { 
$("#ftpbrowser").html(html);
$("#folder").val($("#curfolder").value());
initftpbrowser();
});
}