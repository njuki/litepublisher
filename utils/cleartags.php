<?php
$mode = 'clear';
include('index.php');

function clearlinks(&$tags) {
global $urlmap;
$Urlmap->DeleteClass(get_class($tags));
foreach ($tags->items as $id => $item) {
$tags->AddUrl($id, $item['url']);
}
}

$Urlmap = TUrlmap::Instance();
$Urlmap->Lock();
cleartags(TCategories::Instance());
cleartags(TTags::Instance());
$Urlmap->Unlock();

echo "finished";
?>