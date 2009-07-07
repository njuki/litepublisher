<?php
/* close all comments */
$mode = 'fix';
include('index.php');
echo "<pre>\nStart update from $Options->version\n";
  $Options->commentsenabled = false;
  $Options->pingenabled = false;

$posts = &TPosts::Instance();
foreach ($posts->items as $id => $item) {
$post = &TPost::Instance($id);
$post->commentsenabled = false;
$post->pingenabled = false;
$post->Save();
}

$Urlmap->ClearCache();
echo "comments must be closed";
?>