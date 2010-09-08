<?php

function migrateposts() {
global $data, $man;
$data->load('posts' DIRECTORY_SEPARATOR 'index');
$posts = tposts::instance();
if (dbversion) {
$man->setautoincrement('posts', $data->lastid)
} else {
$posts->autoid = $data->lastid;
}

foreach ($data->data['items'] as $id => $item) {
$post = migratepost($id);
savepost($post);
if (!dbversion) {
      $posts->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $posts->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $posts->items[$post->id]['author'] = $post->author;
}
}
$posts->UpdateArchives();
$posts->save();
}

function migratepost($id) {
global $data;
$data->load('posts' . DIRECTORY_SEPARATOR  . $id . DIRECTORY_SEPARATOR . 'index');
$post = tpost::instance();
foreach ($data->data as $name =>  $value) {
if (isset($post->data[$name])) $post->data[$name] = $value;
}

    $post->posted = $data->date;
$post->idurl = addurl($post->url, $post, $post->id);
return $post;
}

function savepost(tpost $post) {
if (!dbversion) {
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
$post->save();
} else {
insertpost($post);
}
}

function insertpost(tpost $post) {
    $self = tposttransform::instance($post);
    $values = array('id' => $post->id);
    foreach (tposttransform::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = litepublisher::$db;
    $db->table = 'posts';
$db->insert_a($values);
    $post->rawdb->insert_a(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert_a(array('post' => $id, 'page' => $i,         'content' => $content));
    }
    
 }
  
?>