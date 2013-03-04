<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tyoutubefeedInstall($self) {
  $about = tplugins::getabout(tplugins::getname(__file__));
  $admin = tadminmenus::i();
  $idfiles = $admin->url2id('/admin/files/');
  $admin->createitem($idfiles, 'youtube', 'author', 'tadminfiles');
  
tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
  
    $man = tdbmanager::i();
    $man->addenum('files', 'media', 'youtube');

$dir = litepublisher::$paths->files . 'youtube';
@mkdir($dir, 0777);
@chmod($dir, 0666);

copy(dirname(__file__) . '/default.jpg', $dir . '/default.jpg');
@chmod($dir . '/default.jpg', 0666);
      $info = getimagesize($dir . '/default.jpg');

$self->idpreview = tfiles::i()->additem(array(
'filename' => 'youtube/default.jpg',
'title' => 'Default youtube player',
'media' => 'image',
'mime' => 'image/jpeg',
'width' => $info[0],
'height' => $info[1],
));

$self->save();
}

function tyoutubefeedUninstall($self) {
  $admin = tadminmenus::i();
  $admin->deleteurl('/admin/files/youtube/');
  
tthemeparser::i()->unbind($self);

//delete all thumbnails for youtube
$db = $self->getdb('files');
if ($list = implode(',', $db->idselect("media = 'youtube'"))) {
$img = $db->res2assoc($db->select("parent in ($list)"));
foreach ($img as $item) {
@unlink(litepublisher::$paths->files . $item['filename']);
}

$db->delete("id in ($list) or parent in ($list)");
$self->getdb('imghashes')->delete("id in ($list)");
}

    $man = tdbmanager::i();
    $man->delete_enum('files', 'media', 'youtube');

tfiler::delete(litepublisher::$paths->files . 'youtube');
}