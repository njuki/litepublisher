<?php

function Update253() {
$rss = TRSS::Instance();
$rss->Data['template'] = '';
$rss->Save();
}
?>