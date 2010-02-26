<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolls extends titems {
public $userstable;
public $resulttable;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'pols';
    $this->basename = 'pols';
$this->userstable = 'polusers';
$this->resulttable = 'polsresult';'
$this->data['deftitle'] = 'New poll';
  }

public function addvote($idpoll, $iduser, $vote) {
if (!$this->itemexists($id)) return  false;
$vote = (int) $vote;
$db = $this->getdb($this->resulttable)
$db->add(array(
'poll' => $idpoll, 
'user' => $iduser,
'vote' => $vote
));
$table = $db->prefix . $this->votestable;
$res = $db->query("select vote as vote, count(user) as count from $table
where poll = $idpoll  group by vote order by vote asc");

$votes = array();
while($item = $db->fetchassoc($res)) {
$votes[$item['vote']] = $item['count'];
}

$this->db->setvalue($idpoll, 'votes', implode(',', $votes));
return $votes;
}
  
  public function add($title, $status, array $items) {
if ($title == '') $title = $this->deftitle;
if (($status != 'opened') || ($status != 'closed')) $status = 'opened';
    $item = array(
'sign' => md5uniq(),
'title' => $title,
'status' => $status,
'items' => implode("\n", $items)
votes' => implode(',', array_fill(0, count($items), 0))
    );
    
      $id = $this->db->add($item);
      $this->items[$id] = $item;
$this->added($id);
      return $id;
  }

public function edit($id, $title, $status, array $items) {
if (!$this->itemexists)) return false;
$item = $this->getitem($id);
$votes = explode(',', $item['votes']);
if ($title == '') $title = $item['title'];
if (($status != 'opened') || ($status != 'closed')) $status = $item['status'];
if ($title == $item['title']) || ($status == $item['status'])) {
// если равны еще и списки то ничего не менять и вернут false
$old = explode("\n", $item['items']);
if ($old == $items) return false;
if (count($items) > count($old)) {
$votes = array_pad($votes, count($items), 0);
} elseif (count($items) < count($old)) {
$votes = array_slice($votes, 0, count($items));
}
}

    $item = array(
'sign' => $item['sign'],
'title' => $title,
'status' => $status,
'items' => implode("\n", $items)
'votes' => implode(',', $votes)
    );

$this->db->updateassoc($item);
return true;
}

public function hasvote($idpoll, $iduser) {
$idpoll = (int) $idpoll;
$iduser = (int) $iduser;
return $this->getdb($this->votestable)->findid("poll = $idpoll and user = $iduser");
}

public function optimize() {
$signs = $this->db->queryassoc("select id, sign from $this->thistable");
$db = litepublisher::$db;
$posts = tposts::instance();
$db->table = $posts->rawtable;
$deleted = array();
foreach ($signs as $item) {
$sign = $item['sign'];
if (!$db->findid("LOCATE('$sign', rawcontent) > 0")) $deleted[] = $item['id'];
sleep(2);
}

if (count($deleted) > 0) {
$items = sprintf('(%s)', implode(',', $deleted));
$this->db->delete("id in $items");
$this->getdb($this->resulttable)->delete("id in $items");
sleep(2);
}

$db = $this->getdb($this->userstable);
$db->delete("id not in (
select DISTINCT user from $db->prefix.$this->resulttable)");
}

private function extractitems($s) {
$result = array();
$lines = explode("\n", $s);
foreach ($lines as $name) {
$name = trim($name);
if (($name == '')  || ($name[0] == '[')) continue;
$result[] = $name;
}
return $result;
}

private function extractvalues($s) {
$result = array();
$lines = explode("\n", $s);
foreach ($lines as $line) {
$line = trim($line);
if (($line == '')  || ($line[0] == '[')) continue;
      if ($i = strpos($line, '=')) {
        $name = trim(substr($line, 0, $i));
        $value = trim(substr($line, $i + 1));
if (($name != '') && ($value != '')) $result[$name] = $value;
}
}
return $result;
}

 public function beforefilter($idpost, &$content) {
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
$i = 0;
while (is_int($i = strpos($content, '[poll]', $i))) {
$j = strpos($content, '[/poll]', $i);
if ($j == false) {
// значит простая форма и надо найти первую пустую строку
$j = strpos($content, "\n\n", $i);
$s = substr($content, $i, $j - $i);
$items = $this->extractitems($s);
$id = $this->add('', '', $items);
$item = $this->getitem($id);
$stritems = implode("\n", $items);
$replace = "[poll]\nid={$item['sign']}\nstatus={$item['status']}\ntitle={$item['title']}\n[items]\n$stritems\n[/items]\n[/poll]";
$content = substr_replace($content, $replace, $i, $j - $i);
$i = min($j, strlen($content));
} else {
// проверить, если id у голосования
$j += strlen("[/poll]");
$s = substr($content, $i, $j - $i);
// вычленить секцию items
$k = strpos($s, '[items]');
$l = strpos($s, '[/items]');
$items = $this->extractitems(substr($s, $k, $l));
$s = substr_replace($s, '', $k, $l - $k);
$values = $this->extractvalues($s);
$title = isset($values['title'] ? $values['title'] : '';
$status = isset($values['status'] ? $values['status'] : '';
$id = isset($values['id'] ? $this->db->findid("sign = " . dbquote($values['id'])) : false;
if (!$id) {
$id = $this->add($title, $status, $items);
} else {
if (!$this->edit($id, $title, $$status, $items)){
$i = min($j, strlen($content));
continue;
}
}
$item = $this->getitem($id);
$stritems = implode("\n", $items);
$replace = "[poll]\nid={$item['sign']}\nstatus={$item['status']}\ntitle={$item['title']}\n[items]\n$stritems\n[/items]\n[/poll]";
$content = substr_replace($content, $replace, $i, $j - $i);
$i = min($j, strlen($content));
}
}
}
 
  public function filter(&$content) {
// здесь только заменить код голосовалки на html
}

public function getcookie($cookie) {
if (($cookie != '') && ( $iduser = $this->getdb($this->userstable)->findid('cookie = ' .dbquote($cookie)))) {
return $cookie;
}
$cookie = md5uniq();
$this->getdb($this->userstable)->add(array('cookie' => $cookie));
return $cookie;
}

public function sendvote($idpoll, $vote, $cookie) {
if (!$this->itemexists($idpoll)) return $this->error("Poll not found', 404);
$iduser = $this->getdb($this->userstable)->findid('cookie = ' .dbquote($cookie));
if (!$iduser) return $this->error"Cookie not found", 404);
if ($this->hasvote($idpoll, $iduser)) return $this->error('Already you have vote'), 403);
return $this->addvote($idpoll, $iduser, (int) $vote);
}

}//class
?>