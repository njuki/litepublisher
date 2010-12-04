  $(document).ready(function() {
initftpbrowser();
});

function initftpbrowser() {
$("#ftpbrowser").find("a").click(function() {
openftpfolder($(this).value());
});
}

function openftpfolder(folder) {
$.post(ltoptions.url + "/admin/ajaxftpbrowser.htm",
{
host: $("#host").val(),
login: $("#login).val(),
password: ($("#password").val(),
folder: folder
},
function (html) { 
$("#ftpbrowser").html(html);
$("#folder").val($("#curfolder").value());
initftpbrowser();
});
});
}
