<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolls extends tplugin {
  public $items;
  public $voted1;
public $voted2;
  public $templateitems;
  public $templates;
  public $types;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->items = array();
    $this->table = 'polls';
$this->votes = 'votes';
    $this->voted1 = 'pollvotes';
    $this->voted2 = 'pollvoted2';

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
    
  }
  
  public function getvotes() {
    $item = $this->getitem($this->id);

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
    $db = $this->getdb($this->voted1);
    $db->add(array(
    'id' => $id,
    'user' => $iduser,
    ));
    
    $item = $this->getitem($id);
    $votes = explode(',', $item['votes']);
    $table = $db->prefix . $this->voted1;
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
return tpollsman::i()->add($title, $status, $type, $items);
}

  public function edit($id, $title, $status, $type, array $items) {
return tpollsman::i()->edit($id, $title, $status, $type, $items);
}

  public function hasvote($idpoll, $iduser) {
$q = sprintf('id = %d and user = %d', (int) $idpoll, (int) $iduser);
    if ($this->getdb($this->voted1)->findid($q)) return true;
return $this->getdb($this->voted2)->findid($q);
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->getdb($this->voted1)->iddelete($id);
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

public function err($mesg) {
tlocal::usefile('polls');
$lang = tlocal::i('poll');

return array(
'code' => 'error',
'message' => $lang->$mesg
);
}
  
public function polls_sendvote(array $args) {
      extract($args, EXTR_SKIP);
      if (!isset($idpoll) || !isset($vote)) return 403;
$idpoll = (int) $idpoll;
if ($idpoll == 0) return 403;
$vote = (int) $vote;
$iduser = litepublisher::user;
if (!$iduser) return $this->err('notauth');
    if (!$this->itemexists($idpoll)) return $this->err('notfound');
if ('closed' == $this->getvalue($idpoll)) return $this->err('closed');
    if ($this->hasvote($idpoll, $iduser)) return $this->err('voted');

    return $this->addvote($idpoll, $iduser, (int) $vote);
  }
  
}//class