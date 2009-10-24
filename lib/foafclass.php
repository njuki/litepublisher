<?php

class TFoaf extends TItems {
  public $title;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'foaf';
    $this->Data['maxcount'] =0;
    $this->Data['redir'] = true;
    $this->Data['redirlink'] = '/foaflink.php';
  }
  
  public function GetWidgetContent($id) {
    global $Options;
    $Template = TTemplate::Instance();
    $item = !empty($Template->theme['widget']['myfriends']) ? $Template->theme['widget']['myfriends'] :
  '<li><a href=\'$url\' rel=\'friend\'>{$friend[\'nick\']}</a></li>';
    
    $result = '';
    if ($this->maxcount == 0) {
      $list = $this->items;
    } else {
      $list = array_slice($this->items, 0, $this->maxcount, true);
    }
    
    foreach ($list as $id => $friend) {
    $url = $this->redir ?"$Options->url$this->redirlink{$Options->q}friend=$id" : $friend['blog'];
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function Request($arg) {
    global $Options;
    
    switch($arg) {
      case 'xml':
      $s = "<?php
      @header('Content-Type: text/xml; charset=utf-8');
      @ header('Last-Modified: " . date('r') ."');
      @header('X-Pingback: $Options->url/rpc.xml');
      echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>
      '; ?>";
      $s .= $this->GetFoaf();
      return  $s;
      
      case 'redir':
      $this->CacheEnabled = false;
      $id = empty($_GET['friend']) ? 1 : (int) $_GET['friend'];
      if (!isset($this->items[$id])) return 404;
    return "<?php @header('Location: {$this->items[$id]['blog']}'); ?>";
    }
  }
  
  public function Add($nick,$foaf, $blog) {
    $this->items[++$this->autoid] = array(
    'nick' => $nick,
    'foaf' => $foaf,
    'blog' => $blog
    );
    $this->Save();
    $this->Added($this->autoid);
    $Urlmap = &TUrlmap::Instance();
    $Urlmap->ClearCache();
    return $this->autoid;
  }
  
  public function Delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->Save();
      $Urlmap = &TUrlmap::Instance();
      $Urlmap->ClearCache();
    }
  }
  
  public function DeleteUrl($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['blog'])  return $this->Delete($id);
    }
  }
  
  private function GetFoaf() {
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
    
    $profile = &TProfile::Instance();
    $result .= $profile-> GetFoaf();
    $result .= $this->GetKnows();
    
    $result .= '</foaf:Person>
    </rdf:RDF>';
    
    return $result;
  }
  
  private function GetKnows() {
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
  
  public function HasFriend($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['blog']) return true;
    }
    return false;
  }
  
  public function SetParams($maxcount, $redir) {
    if (($this->maxcount != $maxcount) || ($this->redir != $redir)) {
      $this->maxcount = $maxcount;
      $this->redir = $redir;
      $this->Save();
    }
  }
  
}//class

?>