<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfoaf extends titems {
  public $title;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
      $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'foaf';
$this->table = 'foaf';
    $this->data['maxcount'] =0;
    $this->data['redir'] = true;
    $this->data['redirlink'] = '/foaflink.htm';
  }
  
  public function getwidgetcontent($id, $sitebar) {
    $result = '';
    if ($this->dbversion) {
   $limit = $this->maxcount == 0 ? '' : " limit0, $this->maxcount";
    $items = $this->select("status = 'approved'", "order by added desc" . $limit);
    if (!$items) return '';
    } else {
      $items = array_keys($this->items);
if ($this->maxcount != 0) {
      $items = array_slice($items, 0, $this->maxcount);
    }
    }
    
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('foaf', $sitebar);
    $args = targs::instance();
    foreach ($items as $id) {
    $item = $this->getitem($id);
    $args->add($item);
      if ($this->redir && !strbegin($url, litepublisher::$options->url)) {
        $args->url = litepublisher::$options->url . $this->redirlink . litepublisher::$options->q . "id=$id";
      }
      
      $result .=   $theme->parsearg($tml, $args);
    }
        return $result;
  }

    public function request($arg) {
    switch($arg) {
      case 'xml':
      $s = "<?php
      @header('Content-Type: text/xml; charset=utf-8');
      @ header('Last-Modified: " . date('r') ."');
      @header('X-Pingback: litepublisher::$options->url/rpc.xml');
      echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>
      '; ?>";
      $s .= $this->getfoafxml();
      return  $s;
      
      case 'redir':
      $this->cache = false;
      $id = empty($_GET['friend']) ? 1 : (int) $_GET['friend'];
      if (!isset($this->items[$id])) return 404;
    return "<?php @header('Location: {$this->items[$id]['blog']}'); ?>";
    }
  }
  
  public function add($nick,$url, $foafurl, $status) {
  $item = array(
      'url' => $url,
      'foafurl' => $foafurl,
    'nick' => $nick,
    $status => $status
    );

if ($this->dbversion) {
$id = $this->db->add($item);
} else {
$it = ++$this->autoid;
}
    $this->items[$id] = $item;
    $this->save();
    $this->added($id);
    $urlmap = turlmap::instance();
    $urlmap->clearcache();
    return $id;
  }
  
  public function delete($id) {
if (  parent::delete($id)) {
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
      return true;
      }
      return false;
  }
  
  public function deleteurl($url) {
  if ($this->dbversion) {
  $this->db->delete('url = ' . dbquote($url));
  } else {
    foreach ($this->items as $id => $item) {
      if ($url == $item['url'])  return $this->Delete($id);
    }
  }
  
  private function getfoafxml() {
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
  if ($this->dbversion) {
  return $this->select('url = ' . dbquote($url), 'limit 1');
  } else {
    foreach ($this->items as $id => $item) {
      if ($url == $item['url']) return $id;
    }
    return false;
  }
  }
  
  public function setparams($maxcount, $redir) {
    if (($this->maxcount != $maxcount) || ($this->redir != $redir)) {
      $this->maxcount = $maxcount;
      $this->redir = $redir;
      $this->Save();
    }
  }
  
  public function setstatus($id, $value) {
  if ($this->itemexists($id)) $this->setvalue($id, 'status', $value);
  }
  
    public function invate(array $info) {
    if (!$this->samedomain($url)) return false;
    $url = (string) $info['url'];
    if ($this->hasfriend($url)) return false;
    $id = $this->add($info['nick'], $url, (string) $info['foaf'],'hold');
    $this->NotifyModerator($url, 'invated');
    return true;
  }
  
  public function accept(array $info) {
    if (!$this->samedomain($info)) return false;
    $url = (string) $info['blog'];
$id = hasfriend($url));
if (!$id) return false;
$item = $this->getitem($id);
if ($item['status']) == 'approved') return true;
if ($item['status'] != 'invated') return false;
$this->setstatus($id, 'approved');
    $this->NotifyModerator($url, 'accepted');
    return true;
  }
  
    public function reject(array $info) {
    if (!$this->samedomain($info)) return false;
    $url = (string) $info['blog'];
    if ($id = $this->hasfriend($url))  {
$this->delete($id);
      $this->NotifyModerator($url, 'rejected');
      return true;
      }
    return false;
  }
  
  public function addurl($url) {
    if ($ping = tpinger::discover($url)) {
      $actions = TXMLRPCOpenAction::instance();
      if ($actions->invatefriend($ping, $this->GetProfile()) {
        if ($friend = $this->GetFriendInfo($url)) {
          $friend['status'] = 'invated';
          $this->items[$url] = $friend;
          $this->Save();
          return true;
        }
      }
    }
    return false;
  }

}//class

?>