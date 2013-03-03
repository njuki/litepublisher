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
  
  public function getpublished($video) {
    return strtotime($video['published']['$t']);
  }
  
  public function getvideoid($video) {
    return strtr($video['id']['$t'], array(
    'http://www.youtube.com/watch?v=' => '',
    'http://gdata.youtube.com/feeds/api/videos/' => ''
    ));
  }

  public function createthumb($id, array $video) {
    foreach( $video['media$group']['media$thumbnail'] as $item) {
      if (($item['width'] < 200) && ($filename = $this->savethumb($id, $item['url']))) return $filename;
    }
    return false;
  }
  
  public function savethumb($id, $url) {
    if ($s = http::get($url)) {
      $thumbfile = sprintf('%04d/%04d.jpg', floor ($id/ 5000), $id);
      $filename = litepublisher::$paths->files . 'youtubethumbs/' . $thumbfile;
      $dir = dirname($filename);
      if (!is_dir($dir)) {
        mkdir($dir, 0777);
        @chmod($dir, 0777);
      }
      
      file_put_contents($filename, $s);
      @chmod($filename, 0666);
      
      $info = getimagesize($filename);
      $this->getdb('geotube')->updateassoc(array(
      'id' => $id,
      'thumbfile' => $thumbfile,
      'thumbwidth' => $info[0],
      'thumbheight' => $info[1],
      ));
      
      return $filename;
    }
    return false;
  }

  public static function feedtoitems($s) {
    $result = array();
$js = json_decode($s, true);
      foreach ($js['feed']['entry'] as $video) {
        $published = $this->getpublished($video);
        $videoid =$this->getvideoid($video);

        'title' => tcontentfilter::escape(tcontentfilter::unescape($video['title']['$t'])),
        'published' => sqldate($published),

        $this->createthumb($id, $video);

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
      
      $item['filename'] = $id;
      $item['hash'] = $id;

      
      
      $result[$id] = $item;
    }
    return $result;
  }
  
  public function addtofiles(array $item) {
    $files = tfiles::i();
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
    return $id;
  }
  
  public function themeparsed($theme) {
    $theme->templates['content.excerpts.excerpt.filelist.youtube'] = $this->player;
    $theme->templates['content.excerpts.excerpt.filelist.youtubes'] = '$youtube';
    $theme->templates['content.post.filelist.youtube'] = $this->player;
    $theme->templates['content.post.filelist.youtubes'] = '$youtube';
  }
  
}//class