<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolls extends titems {
public $votes;
  public $users1;
public $users2;
  public $templateitems;
  public $templates;
  public $types;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = true;
    parent::create();
$this->basename = 'plugins' . DIRECTORY_SEPARATOR . 'tpolls';
    $this->table = 'polls';
$this->votes = 'pollvotes';
    $this->users1 = 'pollusers1';
    $this->users2 = 'pollusers2';

    $this->addevents('edited');

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

  public function load() {
    return tfilestorage::load($this);
    }

  public function save() {
    if ($this->lockcount > 0) return;
      return tfilestorage::save($this);
    } else {

  public function add($title, $status, $type, array $items) {
return tpollsman::i()->add($title, $status, $type, $items);
}

  public function edit($id, $title, $status, $type, array $items) {
return tpollsman::i()->edit($id, $title, $status, $type, $items);
}

  public function hasvote($idpoll, $iduser) {
$q = sprintf('id = %d and user = %d', (int) $idpoll, (int) $iduser);
    if ($this->getdb($this->users1)->findid($q)) return true;
return $this->getdb($this->users2)->findid($q);
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->getdb($this->users1)->iddelete($id);
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
  
  public function addvote($id, $iduser, $vote) {
$result = array(
'code' => 'success',
'id' => $id,
'total' => 0,
'rate' => 0,
'votes' => array()
);

$db = litepublisher::$db;
$db->query(sprintf('INSERT INTO %s%s (id, user) values %d,%d', $db->prefix, $this->users1, $id, $iduser));
$db->query(sprintf('update %s%s set votes = votes + 1 where id = %d and item = %d', $db->prefix, $this->votes, $id, (int) $vote));

//update stat
$a = $db->res2assoc($db->query(sprintf('select * from %s%s where id = %d', $db->prefix, $this->votes, $id)));
$sum= 0;
foreach ($a as $v) {
$index = (int) $v['item'];
$voted = (int) $v['voted'];
$result['total'] += $voted;
$result['votes'][$index] = $voted;
      $sum += ($index + 1) * $voted;
}
    $result['rate'] = (int) round($sum / count($a) * 10);

        $this->db->updateassoc(array(
    'id' => $id,
    'rate' => $result['rate'],
'total' => $result['total'],
    ));

    return $result;
  }
  
}//class