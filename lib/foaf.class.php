<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tfoaf extends TItems {
  public $title;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'foaf';
    $this->data['maxcount'] =0;
    $this->data['redir'] = true;
    $this->data['redirlink'] = '/foaflink.php';
  }
  
  public function GetWidgetContent($id) {
    global $options;
    $Template = TTemplate::instance();
    $item = !empty($Template->theme['widget']['myfriends']) ? $Template->theme['widget']['myfriends'] :
  '<li><a href=\'$url\' rel=\'friend\'>{$friend[\'nick\']}</a></li>';
    
    $result = '';
    if ($this->maxcount == 0) {
      $list = $this->items;
    } else {
      $list = array_slice($this->items, 0, $this->maxcount, true);
    }
    
    foreach ($list as $id => $friend) {
    $url = $this->redir ?"$options->url$this->redirlink{$options->q}friend=$id" : $friend['blog'];
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function request($arg) {
    global $options;
    
    switch($arg) {
      case 'xml':
      $s = "<?php
      @header('Content-Type: text/xml; charset=utf-8');
      @ header('Last-Modified: " . date('r') ."');
      @header('X-Pingback: $options->url/rpc.xml');
      echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>
      '; ?>";
      $s .= $this->getfoaf();
      return  $s;
      
      case 'redir':
      $this->cache = false;
      $id = empty($_GET['friend']) ? 1 : (int) $_GET['friend'];
      if (!isset($this->items[$id])) return 404;
    return "<?php @header('Location: {$this->items[$id]['blog']}'); ?>";
    }
  }
  
  public function add($nick,$foaf, $blog) {
    $this->items[++$this->autoid] = array(
    'nick' => $nick,
    'foaf' => $foaf,
    'blog' => $blog
    );
    $this->save();
    $this->added($this->autoid);
    $urlmap = turlmap::instance();
    $urlmap->clearcache();
    return $this->autoid;
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    }
  }
  
  public function deleteurl($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['blog'])  return $this->Delete($id);
    }
  }
  
  private function getfoaf() {
    $result = '<rdf:RDF
    xml:lang="en"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
    xmlns:foaf="http://xmlns.com/foaf/0.1/"
    xmlns:ya="http://blogs.yandex.ru/schema/foaf/"
    xmlns:lj="http://www.livejournal.org/rss/lj/1.0/"
    xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
    xmlns:dc="http://purl.org/dc/elements/1.1/">
    <foaf:Person>
    ';
    
    $profile = tprofile::instance();
    $result .= $profile-> getfoaf();
    $result .= $this->getknows();
    
    $result .= '</foaf:Person>
    </rdf:RDF>';
    
    return $result;
  }
  
  private function getknows() {
    $result = '';
    foreach ($this->items as $id => $item) {
      $result .= "<foaf:knows>
      <foaf:Person>
    <foaf:nick>{$item['nick']}</foaf:nick>
    <rdfs:seeAlso rdf:resource=\"{$item['foaf']}\"/>
    <foaf:weblog rdf:resource=\"{$item['blog']}\"/>
      </foaf:Person>
      </foaf:knows>\n";
    }
    
    return $result;
  }
  
  public function hasfriend($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['blog']) return true;
    }
    return false;
  }
  
  public function setparams($maxcount, $redir) {
    if (($this->maxcount != $maxcount) || ($this->redir != $redir)) {
      $this->maxcount = $maxcount;
      $this->redir = $redir;
      $this->Save();
    }
  }
  
}//class

?>