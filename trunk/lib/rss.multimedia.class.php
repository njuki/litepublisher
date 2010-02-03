<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trssMultimedia extends tevents {
  public $domrss;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'rssmultimedia';
    $this->addevents('beforepost', 'afterpost');
    $this->data['feedburner'] = '';
  }
  
  public function fileschanged($idpost) {
    $urlmap = turlmap::instance();
    //    $urlmap->setexpired($this->idcomments);
  }
  
  public function request($arg) {
    global $options, $urlmap;
    $result = "<?php\n";
    if (($arg == null) && ($this->feedburner  != '')) {
      $result .= "if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        if (function_exists('status_header')) status_header( 307 );
        header('Location:$this->feedburner');
        header('HTTP/1.1 307 Temporary Redirect');
        return;
      }
      ";
    }
    
    
    $result .= "  @header('Content-Type: text/xml; charset=utf-8');
    @ header('Last-Modified: " . date('r') ."');
    @header('X-Pingback: $options->url/rpc.xml');
    echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>';
    ?>";
    
    $this->domrss = new Tdomrss;
    $this->domrss->CreateRootMultimedia($urlmap->url, 'media');
    
    $list = $this->getrecent($arg, $options->postsperpage);
    foreach ($list as $id) {
      $this->addfile($id);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  private function getrecent($type, $count) {
    $files = tfiles::instance();
    if (dbversion) {
      $sql = $type == '' ? '' : "media = '$type' ";
      return $files->select($sql . "parent = 0 order by posted desc limit $count");
    } else {
      $result = array();
      $list = array_reverse(array_keys($files->items));
      foreach ($list as $id) {
        $item = $files->items[$id];
        if ($item['parent'] != 0) continue;
        if ($type != '' && $type != $item['media']) continue;
        $result[] = $id;
        if (--$count <= 0) break;
      }
      return $result;
    }
  }
  
  public function addfile($id) {
    global $options;
    $files = tfiles::instance();
    $file = $files->items[$id];
    $posts = $files->itemsposts->getposts($id);
    if (count($posts) == 0) {
      $postlink = $options->url . '/';
    } else {
      $post = Tpost::instance($posts[0]);
      $postllink = $post->link;
    }
    
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $file['title']);
    AddNodeValue($item, 'link', $postlink);
    AddNodeValue($item, 'pubDate', $file['posted']);
    
    $media = AddNode($item, 'media:content');
    AddAttr($media, 'url', $files->geturl($id));
    AddAttr($media, 'fileSize', $file['size']);
    AddAttr($media, 'type', $file['mime']);
    AddAttr($media, 'medium', $file['media']);
    AddAttr($media, 'expression', 'full');
    
    if ($file['width'] > 0 && $file['height'] > 0) {
      AddAttr($media, 'height', $file['height']);
      AddAttr($media, 'width', $file['width']);
    }
    
    if (!empty($file['bitrate'])) AddAttr($media, 'bitrate', $file['bitrate']);
    if (!empty($file['framerate'])) AddAttr($media, 'framerate', $file['framerate']);
    if (!empty($file['samplingrate'])) AddAttr($media, 'samplingrate', $file['samplingrate']);
    if (!empty($file['channels'])) AddAttr($media, 'channels', $file['channels']);
    if (!empty($file['duration'])) AddAttr($media, 'duration', $file['duration']);
    
    $md5 = AddNodeValue($item, 'media:hash', $file['md5']);
    AddAttr($md5, 'algo', "md5");
    
    if (!empty($file['keywords'])) {
      AddNodeValue($item, 'media:keywords', $file['keywords']);
    }
    
    if (!empty($file['description'])) {
      $description = AddNodeValue($item, 'description', $file['description']);
      AddAttr($description, 'type', 'html');
    }
    
    if ($file['preview'] > 0) {
      $preview = $files->getitem($file['preview']);
      $thumbnail  = AddNode($item, 'media:thumbnail');
      AddAttr($thumbnail, 'url', $files->geturl($preview['id']));
      if ($preview['width'] > 0 && $preview['height'] > 0) {
        AddAttr($thumbnail, 'height', $preview['height']);
        AddAttr($thumbnail, 'width', $preview['width']);
      }
    }
    
  }
  
  public function SetFeedburnerLinks($rss, $comments) {
    if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
      $this->feedburner= $rss;
      $this->feedburnercomments = $comments;
      $this->Save();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    }
  }
  
}//class

?>