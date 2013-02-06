<?php

function update548() {
litepublisher::$site->jquery_version = '1.9.1';
  litepublisher::$site->jqueryui_version = '1.10.0';
  litepublisher::$site->save();
  
  $t = ttemplate::i();
  $t->footer = str_replace('2012', '2013', $t->footer);
  $t->save();
  
  $admin = tadminmenus::i();
  $admin->lock();
        $id = $admin->createitem($admin->url2id('/admin/options/'), 'files', 'admin', 'tadminoptions');
        $admin->items[$id]['order'] = $admin->items[$admin->url2id('/admin/options/view/')]['order'];
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