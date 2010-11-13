<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tembeddedplayersInstall($self) {
$self->audio = '<li>
<object type="application/x-shockwave-flash" data="$site.files/js/audio-player/player.swf" id="audioplayer-$id" height="24" width="290">
<param name="movie" value="$site.files/js/audio-player/player.swf" />
<param name="FlashVars" value="playerID=audioplayer1&soundFile=$link" />
<param name="quality" value="high" />
<param name="menu" value="false" />
<param name="wmode" value="transparent" />
</object>
</li>';

$self->video = '<li>
<object type="application/x-shockwave-flash" data="$site.files/js/flowplayer/flowplayer-3.2.5.swf" width="251" height="200" id="videoplayer-$id" name="videoplayer-$id">
<param name="movie" value="$site.files/js/flowplayer/flowplayer-3.2.5.swf" />
<param name="allowfullscreen" value="true" />
<param name="allowscriptaccess" value="always" />
<param name="flashvars" value=\'config={"clip":{"url":"http://start.ru/files/video/28072009.mp4"},"canvas":{"backgroundColor":"#112233"}}\' />
</object>
</li>';

$self->save();
  $parser = tthemeparser::instance();
  $parser->parsed = $self->themeparsed;
  ttheme::clearcache();
}
  
function tembeddedplayersUninstall($self) {
  $parser = tthemeparser::instance();
  $parser->unsubscribeclass($self);
  ttheme::clearcache();
}
?>