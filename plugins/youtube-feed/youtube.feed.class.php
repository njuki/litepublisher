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
    $this->data['idpreview'] = 0;
    $this->data['url'] = '';
    $this->addmap('items', array());
  }
  
  public function getvideoid($video) {
    return strtr($video['id']['$t'], array(
    'http://www.youtube.com/watch?v=' => '',
    'http://gdata.youtube.com/feeds/api/videos/' => ''
    ));
  }

  public function findthumb(array $video) {
if (isset($video['media$group']['media$thumbnail'])) {
echo "yes<br>";
    foreach($video['media$group']['media$thumbnail'] as $item) {
//return first image
return $item['url'];
      //if (($item['width'] < 200) return $item['url'];
    }
} else echo "no<br>";
    return false;
  }
  
  public function parsefeed($url) {
    $result = array();
/*
set_time_limit(90);
$s = file_get_contents('json.txt');
$js = json_decode($s, true);
*/
      if ($s = http::get($url))  {
$js = json_decode($s, true);
} else {return array();}
file_put_contents('json.txt', $s);
      foreach ($js['feed']['entry'] as $video) {
echo implode(', ', array_keys($video)), '<br>';
$videoid = $this->getvideoid($video);
$result[$videoid] = array(
'id' => $videoid,
        'title' => tcontentfilter::escape(tcontentfilter::unescape($video['title']['$t'])),
        'posted' => sqldate(strtotime($video['published']['$t'])),
'thumb' => $this->findthumb($video)
);
    }
    return $result;
  }
  
  public function add($videoid) {
if (!isset($this->items[$videoid])) return false;
    $files = tfiles::i();
if ($id = $files->exists($videoid)) return $id;

$video = $this->items[$videoid];
$idpreview = $this->idpreview;
    if (!empty($video['thumb']) && ($s = http::get($video['thumb']))) {
$parser = tmediaparser::i();
$idpreview = $parser->uploadthumb("youtube/$videoid.jpg", $s);
}

      $item = array(
'filename' => $videoid,
'hash' => $videoid,
'posted' => $video['posted'],
      'media' => 'youtube',
      'mime' => 'application/octet-stream',
'preview' => $idpreview,
'author' => litepublisher::$options->user,
'size' => 0,
'title' => $video['title'],
'description' => '',
'keywords' => ''
      );

    $id = $files->insert($item);

    if ($idpreview != $this->idpreview) {
$files->setvalue($idpreview, 'parent', $id);
    }

    return $id;
  }
  
  public function themeparsed($theme) {
$item = '<span class="image"><a title="$title" rel="youtube" class="youtube-item" id="postfile-$post.id-$id" href="http://www.youtube.com/watch?v=$filename" data-file="$json">$preview</a></span>';
$items = '<div class="files-block filelist-youtube">$youtube</div>';

    $theme->templates['content.excerpts.excerpt.filelist.youtube'] = $item;
    $theme->templates['content.excerpts.excerpt.filelist.youtubes'] = $items;
    $theme->templates['content.post.filelist.youtube'] = $item;
    $theme->templates['content.post.filelist.youtubes'] = $items;
  }
  
}//class