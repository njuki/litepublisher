<?php

function update549() {
$js = tjsmerger::i();
$js->lock();
  $js->deletefile('posteditor', '/js/litepublisher/simpletml.min.js');
  $js->add('default', '/js/litepublisher/simpletml.min.js');
  $js->unlock();

$css = tcssmerger::i();
$css->lock();
$section = 'default';
  $css->deletefile('default', '/js/litepublisher/css/prettyphoto.dialog.css');
  $css->add('default', '/js/litepublisher/css/prettyphoto.dialog.min.css');
      $css->add($section, '/js/litepublisher/css/filelist.min.css');
    $css->add($section, '/js/litepublisher/css/table.min.css');
    $css->addtext('default', 'hidden', '.hidden{display:none}');
$css->unlock();

$files = tfiles::i();
    $files->data['videoplayer'] = '/js/litepublisher/icons/videoplayer.jpg';
        $files->save();

litepublisher::$classes->add('tadminfilethumbnails', 'admin.files.thumbnail.php');

  $admin = tadminmenus::i();
  $admin->lock();
        $id = $admin->createitem($admin->url2id('/admin/files/'), 'THUMBNAIL', 'editor', 'tadminfilethumbnails');
        $admin->items[$id]['order'] = 1;
        $admin->sort();
          $admin->unlock();
          
          $m = tmediaparser::i();
$m->data['audioext'] = 'mp3|wav |flac';
$m->data['videoext'] = 'mp4|ogv|webm';
$m->save();

$themeparser = tthemeparser::i();
        $themeparser->data['stylebefore'] = true;
        $themeparser->save();
}