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
  
  public function getapproved($count) {
      if ($this->dbversion) {
   $limit = $count == 0 ? '' : " limit0, $count";
    if ($result = $this->select("status = 'approved'", "order by added desc" . $limit)) return $result;
    return array();
    } else {
      $iresult = array_keys($this->items);
if ($count > 0) {
      $result = array_slice($items, 0, $this->maxcount);
    }
    return $result;
    }
    }
  
  public function getwidgetcontent($id, $sitebar) {
    $items = $this->getapproved($this->maxcount);
    if (count($items) == 0) return '';
        $result = '';
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
      'nick' => $nick,
      'url' => $url,
      'foafurl' => $foafurl,
'added' => sqldate(),
'errors' => 0,
    'status' => $status
    );

if ($this->dbversion) {
$id = $this->db->add($item);
} else {
$it = ++$this->autoid;
}
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
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

  private function getdomain($Url) {
    $Url = strtolower(trim($Url));
    if (preg_match('/(http:\/\/|https:\/\/|)(www\.|)([-\.\w]+)\/?/', $Url, $Found)) {
      return isset($Found[3]) && !empty($Found[3]) ? $Found[3] : false;
    }
    return false;
  }
  
    private function validateurl($url, $foafurl) {
      if ($url = $this->getdomain($url) && $foafurl = $this->getdomain($foafurl)) {
      $self = $this->getdomain(litepublisher::$options->url);
      return ($url == $foafurl) && ($url != $self);
    }
return false;
  }
  
    public function invate($nick,$url, $foafurl) {
    if (!$this->validateurl($url, $foafurl)) return false;
    if ($this->hasfriend($url)) return false;
    $id = $this->add($nick,$url, $foafurl, 'hold');
    $util = tfoafutil::instance();
    $util->NotifyModerator($url, 'invated');
    return true;
  }
  
  public function accept($nick,$url, $foafurl) {
    if (!$this->validateurl($url, $foafurl)) return false;
$id = hasfriend($url));
if (!$id) return false;
$item = $this->getitem($id);
if ($item['status']) == 'approved') return true;
if ($item['status'] != 'invated') return false;
$this->setstatus($id, 'approved');
    $this->NotifyModerator($url, 'accepted');
    return true;
  }
  
    public function reject($nick,$url, $foafurl) {
    if (!$this->validateurl($url, $foafurl)) return false;
    if ($id = $this->hasfriend($url))  {
$this->delete($id);
      $this->NotifyModerator($url, 'rejected');
      return true;
      }
    return false;
  }
  
      private function getprofile() {
    $profile = tprofile::instance();
    return array(
        'nick' => $profile->nick,
        'url' => litepublisher::$options->url . litepublisher::$options->home,
            'foafurl' => litepublisher::$options->url . '/foaf.xml'
    );
  }
  
  public function addurl($url) {
    if ($ping = tpinger::discover($url)) {
      $actions = TXMLRPCOpenAction::instance();
      if ($actions->invatefriend($ping, $this->profile)) {
      $util = tfoafutil::instance();
        if ($info = $util->getinfo($url)) {
          return $this->add($info['nick'], $info['url'], $info['foafurl'], 'invated');
        }
      }
    }
    return false;
  }
  
    public function acceptinvate($id) {
if (!$this->itemexists($id)) return false;
$item = $this->getitem($id);
        if ($ping = tpinger::Discover($item['url'])) {
      $actions =  TXMLRPCOpenAction::instance();
      if ($actions->acceptfriend($ping, $this->profile)) {
$this->setstatus($id, 'approved');
        return true;
      }
    }
    return false;
  }
  
  public function rejectinvate($id) {
if (!$this->itemexists($id)) return false;
$item = $this->getitem($id);
$this->setstatus($id, 'rejected');
        if ($ping = tpinger::Discover($item['url'])) {
      $actions =  TXMLRPCOpenAction::instance();
      if ($actions->rejectfriend($ping, $this->profile)) {
        return true;
      }
    }
    return false;
  }
  
}//class

?>