<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolls extends tplugin {
  public $items;
  public $votestable;
public $votes2;
  public $templateitems;
  public $templates;
  public $types;
  private $id;
  private $curvote;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->items = array();
    $this->table = 'polls';
    $this->votestable = 'pollvotes';
    $this->votes2 = 'pollvotes2';
    $this->addevents('added', 'deleted', 'edited');
    $this->data['garbage'] = true;
    $this->data['deftitle'] = 'new poll';
    $this->data['deftype'] = 'star';
    $this->data['defitems'] = 'Yes,No';
    $this->data['defadd'] = false;
    $this->data['voted'] = '';
    $this->types = array('star', 'radio', 'button', 'link', 'custom');
    $a = array_combine($this->types, array_fill(0, count($this->types), ''));
    $this->addmap('templateitems', $a);
    $this->addmap('templates', $a);
    
    $this->id = 0;
    $this->curvote = 0;
  }
  
  public function __get($name) {
    if (strbegin($name, 'start_')) {
      $id = (int) substr($name, strlen('start_'));
      if (($id > 0) && $this->itemexists($id)) {
        $this->id = $id;
        $this->curvote = 0;
        ttheme::$vars['pull'] = $this;
      }
      return '';
    }
    if ($name == 'end') return '';
    
    return parent::__get($name);
  }
  
  public function getvotes() {
    $item = $this->getitem($this->id);
    $votes = explode(',', $item['votes']);
    if ($this->curvote >= count($votes)) return 0;
    return $votes[$this->curvote++];
  }
  
  public function getitem($id) {
    if (isset($this->items[$id])) return $this->items[$id];
    if ($this->select("$this->thistable.id = $id", 'limit 1')) return $this->items[$id];
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function select($where, $limit) {
    if ($where != '') $where = 'where '. $where;
    $res = $this->db->query("SELECT * FROM $this->thistable $where $limit");
    return $this->res2items($res);
  }
  
  public function res2items($res) {
    if (!$res) return false;
    $result = array();
    
    while ($item = litepublisher::$db->fetchassoc($res)) {
      $id = $item['id'];
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function itemexists($id) {
    if (isset($this->items[$id])) return true;
    try {
      return $this->getitem($id);
    } catch (Exception $e) {
      return false;
    }
    return false;
  }
  
  public function getrate(array $votes){
    $sum = 0;
    foreach ($votes as $i => $count) {
      $sum += ($i + 1) * $count;
    }
    return (int) round($sum / count($votes) * 10);
  }
  
  public function addvote($id, $iduser, $vote) {
    if (!$this->itemexists($id)) return  false;
    $vote = (int) $vote;
    $db = $this->getdb($this->votestable);
    $db->add(array(
    'id' => $id,
    'user' => $iduser,
    'vote' => $vote
    ));
    
    $item = $this->getitem($id);
    $votes = explode(',', $item['votes']);
    $table = $db->prefix . $this->votestable;
    $res = $db->query("select vote as vote, count(user) as count from $table
    where id = $id group by vote order by vote asc");
    
    while($item = $db->fetchassoc($res)) {
      $votes[$item['vote']] = $item['count'];
    }
    
    $this->db->updateassoc(array(
    'id' => $id,
    'rate' => $this->getrate($votes),
    'votes' =>  implode(',', $votes)
    ));
    return $votes;
  }
  
  public function add($title, $status, $type, array $items) {
    if ($title == '') $title = $this->deftitle;
    if (($status != 'opened') || ($status != 'closed')) $status = 'opened';
    if (!in_array($type, $this->types)) $type = $this->deftype;
    $votes = implode(',', array_fill(0, count($items), 0));
    $item = array(
    'hash' => md5uniq(),
    'title' => $title,
    'status' => $status,
    'type' => $type,
    'items' => implode("\n", $items),
    'votes' => $votes,
    'rate' => 0
    );
    
    $id = $this->db->add($item);
    $this->items[$id] = $item;
    $this->added($id);
    return $id;
  }
  
  public function edit($id, $title, $status, $type, array $items) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $votes = explode(',', $item['votes']);
    $rate = $this->getrate($votes);
    if ($title == '') $title = $item['title'];
    if (($status != 'opened') || ($status != 'closed')) $status = $item['status'];
    if (!in_array($type, $this->types)) $type = $item['type'];
    if (($title == $item['title']) && ($status == $item['status']) && ($type == $item['type'])) {
      // if items is equal them return false
      $old = explode("\n", $item['items']);
      if ($old == $items) return false;
      if (count($items) > count($old)) {
        $votes = array_pad($votes, count($items), 0);
      } elseif (count($items) < count($old)) {
        $votes = array_slice($votes, 0, count($items));
      }
    }
    
    $item = array(
    'id' =>  $id,
    'rate' => $rate,
    'hash' => $item['hash'],
    'title' => $title,
    'status' => $status,
    'type' => $type,
    'items' => implode("\n", $items),
    'votes' => implode(',', $votes)
    );
    
    $this->db->updateassoc($item);
    return true;
  }
  
  public function hasvote($idpoll, $iduser) {
    $idpoll = (int) $idpoll;
    $iduser = (int) $iduser;
    return $this->getdb($this->votestable)->findid("id = $idpoll and user = $iduser");
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->getdb($this->votestable)->iddelete($id);
  }
  
  public function optimize() {
    $this->externalfunc(get_class($this), 'Optimize', null);
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
  
  public function beforefilter($post, &$content) {
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $i = 0;
    while (is_int($i = strpos($content, '[poll]', $i))) {
      $j = strpos($content, '[/poll]', $i);
      if ($j == false) {
        // simple form and need to find empty string
        $j = strpos($content, "\n\n", $i);
        $s = substr($content, $i, $j - $i);
        $items = $this->extractitems($s);
        $id = $this->add('', '', '', $items);
      } else {
        // has poll id?
        $j += strlen("[/poll]");
        $s = substr($content, $i, $j - $i);
        // extract items section
        $k = strpos($s, '[items]');
        $l = strpos($s, '[/items]');
        $items = $this->extractitems(substr($s, $k, $l));
        $s = substr_replace($s, '', $k, $l - $k);
        $values = $this->extractvalues($s);
        $title = isset($values['title']) ? $values['title'] : '';
        $status = isset($values['status']) ? $values['status'] : '';
        $type = isset($values['type']) ? $values['type'] : '';
        $id = isset($values['id']) ? $this->db->findid("hash = " . dbquote($values['id'])) : false;
        if (!$id) {
          $id = $this->add($title, $status, $type, $items);
        } else {
          if (!$this->edit($id, $title, $status, $type, $items)){
            $i = min($j, strlen($content));
            continue;
          }
        }
      }
      //common for both cases
      $item = $this->getitem($id);
      $stritems = implode("\n", $items);
    $replace = "[poll]\nid={$item['hash']}\n";
$replace .= "status={$item['status']}\ntype={$item['type']}\ntitle={$item['title']}\n";
      $replace .= "[items]\n$stritems\n[/items]\n[/poll]";
      
      $src = substr($content, $i, $j - $i);
      $content = substr_replace($content, $replace, $i, $j - $i);
      $post->rawcontent = str_replace($src, $replace, $post->rawcontent);
      $i = min($j, strlen($content));
    }
  }
  
  public function filter(&$content) {
    //replace poll templates to html
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $i = 0;
    while (is_int($i = strpos($content, '[poll]', $i))) {
      $j = strpos($content, '[/poll]', $i);
      $j += strlen("[/poll]");
      $s = substr($content, $i, $j - $i);
      // extract items
      $k = strpos($s, '[items]');
      $l = strpos($s, '[/items]');
      $s = substr_replace($s, '', $k, $l - $k);
      $values = $this->extractvalues($s);
      $id = isset($values['id']) ? $this->db->findid("hash = " . dbquote($values['id'])) : false;
      if ($id) {
        $replace = $this->gethtml($id, false);
        $content = substr_replace($content, $replace, $i, $j - $i);
      }
      $i = min($j, strlen($content));
    }
  }
  
  public function geterror_dialog() {
    //jquery ui dialog template
    return sprintf('<div class="poll_error_dialog" style="display: none;" title="%s"><h4>%s</h4></div>', tlocal::get('default', 'error'), $this->voted);
  }
  
  public function gethtml($id, $full) {
    $result = '';
    $dialog = $this->geterror_dialog();
    $poll = $this->getitem($id);
    $items = explode("\n", $poll['items']);
    $votes = explode(',', $poll['votes']);
    $theme = ttheme::i();
    $args = targs::i();
    $args->id = $id;
    $args->title = $poll['title'];
    if (!$full) $args->votes = '&#36;poll.votes';
    $tml = $this->templateitems[$poll['type']];
    foreach ($items as $index => $item) {
      $args->checked = 0 == $index;
      $args->index = $index;
      $args->item = $item;
      if ($full) $args->votes = $votes[$index];
      $result .= $theme->parsearg($tml, $args);
    }
    $args->items = $full ? $result : sprintf('&#36;poll.start_%d %s &#36;poll.end', $id, $result);
    $tml = $this->templates[$poll['type']];
    $result = $theme->parsearg($tml, $args);
    
    if ($poll['rate'] > 0) {
      $args->votes = array_sum($votes);
      $args->rate =1 + $poll['rate'] / 10;
      $args->worst = 1;
      $args->best = count($items);
      $result .= $theme->parsearg($this->templates['microformat'], $args);
    }
    
    return str_replace(array("'", '&#36;'), array('"', '$'),
    $dialog . $result);
  }
  
  public function gethead() {
    $template = ttemplate::i();
    return $template->getready('if ($("*[id^=\'pollform_\']").length) {
      $.load_script(ltoptions.files + "/plugins/polls/polls.client.js");
    });');
  }
  
public function polls_sendvote(array $args) {
      extract($args, EXTR_SKIP);
$iduser = litepublisher::user;
if (!$iduser) return 403;
      if (!isset($idpoll) || !isset($vote)) return 403;
    if (!$this->itemexists($idpoll)) return $this->error('poll not found', 404);

    if ($this->hasvote($idpoll, $iduser)) return $this->error($this->voted, 403);

    return $this->addvote($idpoll, $iduser, (int) $vote);
  }
  
  public function setdefadd($v) {
    if ($v == $this->defadd) return;
    $this->data['defadd'] = $v;
    $this->data['garbage'] = ! $v;
    $this->save();
    $posts = tposts::i();
    if ($v) {
      $posts->added = $this->postadded;
      $posts->deleted = $this->postdeleted;
      $posts->aftercontent = $this->afterpost;
      $posts->syncmeta = true;
    } else {
      $posts->delete_event_class('added', get_class($this));
      $posts->delete_event_class('deleted', get_class($this));
      $posts->delete_event_class('aftercontent', get_class($this));
    }
  }
  
  public function postadded($idpost) {
    $post = tpost::i($idpost);
    $post->meta->poll = $this->add($this->deftitle, 'opened', $this->deftype, explode(',', $this->defitems));
  }
  
  public function afterpost(tpost $post, &$content) {
    if (isset($post->meta->poll)) {
      $content = $this->gethtml($post->meta->poll, true) . $content;
    }
  }
  
  public function postdeleted($id) {
    if (!dbversion) return;
    $meta = tmetapost::i($id);
    if (isset($meta->poll)) {
      $this->delete($meta->poll);
    }
  }
  
}//class