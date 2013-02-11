<?php

function update549() {
$css = tcssmerger::i();
$css->lock();
$section = 'default'';
  $css->deletefile('default', '/js/litepublisher/css/prettyphoto.dialog.css');
  $css->add('default', '/js/litepublisher/css/prettyphoto.dialog.min.css');
      $css->add($section, '/js/litepublisher/css/filelist.min.css');
    $css->add($section, '/js/litepublisher/css/table.min.css');
    $css->addtext('default', 'hidden', '.hidden{display:none}');
$css->unlock();

$files = tfiles::i();
    $files->data['videoposter'] = false;
    if ($img = http::get('http://litepublisher.ru/files/image/videoposter-big.jpg')) {
        $files->data['videoposter'] = tmediaparser::i()->uploadthumbnail('videoposter.jpg', $img);
    }
        $files->save();

litepublisher::$classes->add('tadminfilethumbnails', 'admin.files.thumbnail.php');

  $admin = tadminmenus::i();
  $admin->lock();
        $id = $admin->createitem($admin->url2id('/admin/files/'), 'THUMBNAIL', 'editor', 'tadminfilethumbnails');
        $admin->items[$id]['order'] = 1;
        $admin->sort();
          $admin->unlock();
          
          $m = tmediaparser::i();
          $m->data['quality_snapshot'] = 95;
 $m->data['quality_original'] = 95;
$m->data['audioext'] = 'mp3|wav |flac|f4a|f4b';
$m->data['videoext'] = 'mp4|mpe|mpeg|mpg|avi|mov|ogv|webm|flv|f4v|f4p';
        unset($m->data['audiosize']);
          $m->save();
          
          $man = tdbmanager::i();
          $files = tfiles::i();
                    $man->alter($files->table, "drop mime");
          $man->alter($files->table, "add 
                mime enum ('application/octet-stream', 'image/jpeg', 'image/png', 'image/gif', 'image/bmp',
       'image/x-icon', 'image/tiff', 'image/vnd.wap.wbm', 'image/x-xbitmap', 'image/pict',
      'audio/mpeg', 'audio/mp44', 'audio/x-wav', 'audio/ogg',
      'video/mp4', 'video/mpeg', 'video/ogg', 'video/webm', 'video/x-flv',
      'text/plain'
      ) default 'application/octet-stream'
      after media");

    $mime = array(
    'txt' => 'text/plain',

'jpg' => 'image/jpeg',
'jpe' => 'image/jpeg',
'peg' => 'image/jpeg',

'png' =>  'image/png',
 'gif' => 'image/gif',
 'bmp' =>  'image/bmp',
       'ico' => 'image/x-icon',
        'tif' => 'image/tiff',
        
         'xbm' => 'image/x-xbitmap',
          'pct' => 'image/pict',
                    'pic' => 'image/pict',
          
        'mp4' => 'video/mp4',
'mpe' => 'video/mpeg',
'peg' => 'video/mpeg',
'mpg' => 'video/mpeg',
'avi' => 'video/x-msvideo',
'mov' => 'video/quicktime',
'ogv' => 'video/ogg',
'ebm' => 'video/webm',
'flv' => 'video/x-flv',
'f4v' => 'video/mp4',
'f4p' => 'video/mp4',

    'mp3' => 'audio/mpeg',
    'wav' => 'audio/x-wav', 
'lac' => 'audio/ogg',
'f4a' => 'audio/mp4',
'f4b' => 'audio/mp4',
    );

$db = $files->db;
    $items = $db->res2assoc($db->query("select id, filename from $files->thistable"));
foreach ($items as $item) {
$ext = substr($item['filename'], -3);
$db->setvalue($item['id'], 'mime', isset($mime[$ext]) ? $mime[$ext] : 'application/octet-stream');
}
}