<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyoutubefeed extends tevents {

  public static function instance() {
    return getinstance(__class__);
  }
  
public static function feedtoitems($s) {
$result = array();
$xml = new SimpleXMLElement($s);
    foreach ($xml->entry as $entry) {
$item = array(
'media' => 'youtube',
'mime' => 'application/x-shockwave-flash',
'parent' => 0,
'preview' => 0,
'icon' => 0,
'author' => litepublisher::$options->user,
'size' => 0
);
$item = array();
$id = substr($entry->id, strrpos($entry->id, '/') + 1);
$item['filename'] = $id;
$item['md5'] = $id;
$item['posted'] = sqldate(strtotime($entry->published));

      $media = $entry->children('http://search.yahoo.com/mrss/');
$group = $media->group;
$item['title'] = (string) $group->title;
$item['description'] = (string) $group->description;
$item['keywords'] = (string) $group->keywords;

      $attrs = $group->thumbnail[0]->attributes();
      $item['preview'] = (string) $attrs['url']; 
$result[$id] = $item;
}
return $result;
}

public function addtofiles(array $item) {
$files = tfiles::instance();
$files->lock();
if ($image = http::get($item['preview']) {
$filename = 'thumbnail.' . substr($item['preview'], strrpos($item['preview'], '/') + 1);
$item['preview'] = $files->uploadthumbnail($filename, $image);
} else {
$item['preview'] = 0;
}
$id = $files->additem($item);
if ($item['preview'] != 0) $files->setvalue($item['preview'], 'parent', $id);
$files->unlock();
return $id;
}

}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}
date_default_timezone_set('Europe/Moscow' );
$feed = new tyoutubefeed();
$s = file_get_contents('Most Viewed.xml');
$items = $feed->feedtoitems($s);

var_dump($items);
?>