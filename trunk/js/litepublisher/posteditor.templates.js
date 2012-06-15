(function( $ ){
$.posteditor.templates = {
item: '<div class="file-item">\
<span class="value-title" title="{{id}}"/>\
<div class="file-toolbar">\
{{toolbar}}\
</div>\
<div class="file-content">\
{{content}}\
</div>\
</div>',

toolbar: '<a href="#" title="{{title}}" class="{{class}}"><img src="{{url}}" title="{{title}}" alt="{{title}}" /></a>',

image: '',

tabs: '<div id="upload"><span id="uploadbutton"></span></div>\
<div id="progressbar"></div>\
$fileperm\
<div id="posteditor-files-tab">\
    <ul>\
        <li><a href="#currentfilestab"><span>$lang.currentfiles</span></a></li>
        <li><a href="#newfilestab"><span>$lang.newupload</span></a></li>
$pages
    </ul>

<div id="currentfilestab">
$currentfiles
</div>

<div id="newfilestab"></div>
</div>
[hidden=files]"

pageindex: '<li><a href="$ajax=filepage&page=$index"><span>$index</span></a></li>',
page: ''
};

})( jQuery );

