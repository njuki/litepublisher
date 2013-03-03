<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
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
  }
  
  public function getvideoid($video) {
    return strtr($video['id']['$t'], array(
    'http://www.youtube.com/watch?v=' => '',
    'http://gdata.youtube.com/feeds/api/videos/' => ''
    ));
  }

  public function findthumb($id, array $video) {
    foreach( $video['media$group']['media$thumbnail'] as $item) {
      if (($item['width'] < 200) return $item['url'];
    }
    return false;
  }
  
  public static function parsefeed($url) {
    $result = array();
      if ($s = http::get($url))  {
$js = json_decode($s, true);
} else return array();

      foreach ($js['feed']['entry'] as $video) {
$result[] = array(
'id' => $this->getvideoid($video),
        'title' => tcontentfilter::escape(tcontentfilter::unescape($video['title']['$t'])),
        'posted' => sqldate(strtotime($video['published']['$t'])),
'thumb' => $this->findthumb($item)
);
    }
    return $result;
  }
  
  public function add(array $video) {
    $files = tfiles::i();
    if (!empty($item['preview']) && ($image = http::get($item['preview']))) {
      $item = array(
      'media' => 'youtube',
      'mime' => 'application/x-shockwave-flash',
      );

      $ext = substr($item['preview'], strrpos($item['preview'], '.'));
      $filename = sprintf('thumbnail.%s%s', $item['filename'], $ext);
      $mediaparser = tmediaparser::i();
      $item['preview'] = $mediaparser->uploadthumbnail($filename, $image);
    } else {
      $item['preview'] = 0;
    }
    
    $id = $files->insert($item);
    if ($item['preview'] != 0) $files->setvalue($item['preview'], 'parent', $id);
    return $id;
  }
  
  public function themeparsed($theme) {
$item = '<span class="image"><a title="$title" rel="youtube" class="youtube-item" id="postfile-$post.id-$id" href="http://www.youtube.com/watch?v=$hash" data-file="$json">$preview</a></span>';
$items = '<div class="files-block filelist-youtube">$youtube</div>';

    $theme->templates['content.excerpts.excerpt.filelist.youtube'] = $item;
    $theme->templates['content.excerpts.excerpt.filelist.youtubes'] = $items;
    $theme->templates['content.post.filelist.youtube'] = $item;
    $theme->templates['content.post.filelist.youtubes'] = $items;
  }
  
}//class