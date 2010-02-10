<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlinkswidget extends titems {
  public $redirlink;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widgets.links';
    $this->redirlink = '/linkswidget/';
    $this->data['redir'] = true;
  }
  
  public function getwidgetcontent($id, $sitebar) {
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('link', $sitebar);
    $args = targs::instance();
    foreach ($this->items as $id => $item) {
      $url =  $item['url'];
      if ($this->redir && !strbegin($url, litepublisher::$options->url)) {
        $url = litepublisher::$options->url . $this->redirlink . litepublisher::$options->q . "id=$id";
      }
      
      $result .=   sprintf($tml, $url, $item['title'], $item['text']);
    }
    
    return $result;
  }
  
  public function add($url, $title, $text) {
    $this->items[++$this->autoid] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->save();
    $this->added($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $url, $title, $text) {
    $this->items[$id] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->save();
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
  return "<?php @header('Location: {$this->items[$id]['url']}'); ?>";
  }
  
}//class

?>