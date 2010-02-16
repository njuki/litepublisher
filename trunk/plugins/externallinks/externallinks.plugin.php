<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class texternallinks extends titems {

 public static function instance() {
  return getinstance(__class__);
 }
 

protected function create() {
$this->dbversion = dbversion;
parent::create();
$this->table = 'externallinks';
$this->basename = 'externallinks';
}

public function add($url) {
$id = $this->IndexOf('url', $url);
if ($id > 0) return $id;
$item = array(
'url' => $url,
'clicked' = 0
);

if ($this->dbversion) {
return $this->add($item);
} else {
$this->items[++$this->autoid]  = $item;
$this->save();
return $this->autoid;
}
}

public function request($arg) {
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$this->itemexists($id)) return 404;
$item = $this->getitem($id);
if ($this->dbversion) {
$this->db->setvalue($id, 'clicked', $item['clicked'] + 1);
} else {
$this->items[$id]['clicked']++;
$this->save();
}
turlmap::redir($item['url']);
}

public function filter(&$content) {
			if(!preg_match_all("/<a\\s*.*?href\\s*=\\s*['\"]([^\"'>]*).*?>(.*?)<\/a>/i", $content, $links))  return;
$redir = litepublisher::$options->url . '/externallink.htm' . litepublisher::$options->q . 'id=';
$external = array();
				foreach($links[1] as $num => $link) {
if (isset($external[$links])) continue;
if (strbegin($link, litepublisher::$options->url)) continue;
$id = $this->add($link);
$external[$link] = $redir . $id;
    }

if (count($external) > 0) $content = strtr($content, $external);
  }

}

}//class
?>