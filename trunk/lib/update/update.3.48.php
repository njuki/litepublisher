<?php
function update348() {
$files = tfiles::instance();
  $posts= tposts::instance();
  $posts->lock();
  $posts->added = $files->postedited;
  $posts->edited = $files->postedited;
  $posts->deleted = $files->itemsposts->deletepost;
  $posts->unlock();
}

?>