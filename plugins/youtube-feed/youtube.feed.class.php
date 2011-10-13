<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyoutubefeed extends tplugin {
  public $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  protected function create() {
    parent::create();
    $this->data['url'] = '';
    $this->addmap('items', array());
    $this->data['player'] ='<li><object width="425" height="350">' .
    '<param name="movie" value="http://www.youtube.com/v/$filename?fs=1&amp;rel=0"></param>' .
    //'<param name="wmode" value="transparent"></param>' .
    '<param name="allowFullScreen" value="true"></param>' .
    '<param name="allowscriptaccess" value="always"></param>' .
    '<embed src="http://www.youtube.com/v/$filename?fs=1&amp;rel=0" ' .
    'type="application/x-shockwave-flash" ' .
    //'wmode="transparent" ' .
    'allowscriptaccess="always" ' .
    'allowfullscreen="true" ' .
    'width="425" height="350">' .
    '</embed></object></li>';
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
      'size' => 0,
'description' => '',
'keywords' => '',
'preview' => ''
      );

      $id = substr($entry->id, strrpos($entry->id, '/') + 1);
      $item['filename'] = $id;
      $item['hash'] = $id;
      $item['posted'] = sqldate(strtotime($entry->published));
      $item['title'] = (string) $entry->title;      

/*
      $media = $entry->children('http://search.yahoo.com/mrss/');
      $group = $media->group;
      //$item['title'] = (string) $group->title;
      $item['description'] = (string) $group->description;
      $item['keywords'] = (string) $group->keywords;
  dumpvar($item);    
      $attrs = $group->thumbnail[0]->attributes();
      $item['preview'] = (string) $attrs['url'];
*/

      $result[$id] = $item;
    }
    return $result;
  }
  
  public function addtofiles(array $item) {
    $files = tfiles::i();
    $files->lock();
    if (!empty($item['preview']) && ($image = http::get($item['preview']))) {
      $ext = substr($item['preview'], strrpos($item['preview'], '.'));
      $filename = sprintf('thumbnail.%s%s', $item['filename'], $ext);
      $mediaparser = tmediaparser::i();
      $item['preview'] = $mediaparser->uploadthumbnail($filename, $image);
    } else {
      $item['preview'] = 0;
    }
    
    $id = $files->insert($item);
    if ($item['preview'] != 0) $files->setvalue($item['preview'], 'parent', $id);
    $files->unlock();
    return $id;
  }
  
  public function themeparsed($theme) {
    $theme->templates['content.excerpts.excerpt.filelist.youtube'] = $this->player;
    $theme->templates['content.excerpts.excerpt.filelist.youtubes'] = '$youtube';
    $theme->templates['content.post.filelist.youtube'] = $this->player;
    $theme->templates['content.post.filelist.youtubes'] = '$youtube';
  }
  
}//class