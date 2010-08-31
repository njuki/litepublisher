<?php

class tyoutube extends toauth {
private $devkey;
  
  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'youtube' . DIRECTORY_SEPARATOR . 'index';
    $this->devkey = 'AI39si4_m8rJy-W_aoj8IB--x80qAWN1MEoIqclupj_mulksGFDiNz2tIxVLQAnXMPYA8adauF45HFxKQV9WiQtbCJV64ZFmDw';
    $this->urllist['callback'] = litepublisher::$options->url . '/admin/youtube/accesstoken.htm';
    $this->urllist['gettokenupload'] = 'http://gdata.youtube.com/action/GetUploadToken';
  }
  
  public function getkeys() {
    return array('scope' => 'http://gdata.youtube.com');
  }
  
  public function getextraheaders() {
    return array(
    'Content-Type: application/atom+xml; charset=UTF-8',
    'GData-Version: 2',
    'X-GData-Key: key=' . $this->devkey
    );
  }
  
  public function xmlrpcgetuploadtoken($login, $password, $title, $description, $category, $keywords) {
    TXMLRPCAbstract::auth($login, $password, 'editor');
    
    if ($xml = $this->getuploadtoken($title, $description, $category, $keywords)) {
      return array(
      'url' => $xml->url,
      'token' => $xml->token
      );
    }
    //fix for javascript client library
    return 'false';
  }
  
  public function getuploadtoken($title, $description, $category, $keywords) {
    $s = '<?xml version="1.0" encoding="utf-8"?>
    <!--generator="Lite Publisher-->
    <entry xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://gdata.youtube.com/schemas/2007">
    <media:group>
    <media:title type="plain"></media:title>
    <media:description type="plain"></media:description>
    <media:category scheme="http://gdata.youtube.com/schemas/2007/categories.cat"></media:category>
    <media:keywords></media:keywords>
    </media:group>
    </entry>';
    
    $xml = new SimpleXMLElement($s);
    $media = $xml->children('http://search.yahoo.com/mrss/');
    $group = $media->group;
    $group->title = $title;
    $group->description = $description;
    $group->category  = $category;
    $group->keywords = $keywords;
    
    $postdata = $xml->asXML();
    if ($response = $this->oauth->postdata($postdata, $this->oauth->urllist['gettokenupload']))  return simplexml_load_string($response);    return false;
  }

public static function getlang() {
tlocal::load(dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . litepublisher::$options->languages . '.youtube');
return tlocal::instance('youtube');
}
  
  public function uploaded() {
$lang = self::getlang();
    if (empty($_GET['status']) || empty($_GET['id']) ||($_GET['status'] != 200))  return tsimplecontent::content($lang->notuploaded);
//add id to files
$files = tfiles::instance();
return turlmap::redir301('/admin/files/youtube/');
  }
  
  public function request($arg) {
    if ($s = tadminmenu::auth('editor')) return $s;

    switch ($arg) {
case 'request':
if ($url = $this->getrequesttoken()) {
return turlmap::redir($url);
}
return 404;

      case 'access':
      if (!empty($_GET['oauth_token']) && $this->getaccesstoken()) {
return  turlmap::redir301('/admin/files/youtube/');
}
return 404;
      
      case 'uploaded':
      return $this->uploaded();
    }
  }
  
}//class

class tyoutubecategories extends titems {
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'youtube' . DIRECTORY_SEPARATOR . 'categories';
  }
  
  public function update() {
    $url = 'http://gdata.youtube.com/schemas/2007/categories.cat';
    $lang = litepublisher::$options->languages;
    if ($lang != 'en') {
      $url .= sprintf('?hl=%s-%s', $lang, strtoupper($lang));
    }
    if (s = http://get($url)) {
      $xml = simplexml_load_string($s);
      $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
      $categories = $xml->xpath('//atom:category');
      $this->items = array();
      foreach ($categories as $cat) {
        if ($yt = $cat->children('yt', true)->getName() == 'assignable') {
          $this->items[(string) $cat['term']] = (string) $cat['label'];
        }
      }
      $this->save();
    }
  }
  
}//class

?>