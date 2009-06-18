<?php

function Update192() {
 global $Options, $Urlmap;
 $Options->q = '?';
 $Urlmap->UnsubscribeEvent('CacheExpired', 'TCommentForm');
}

?>