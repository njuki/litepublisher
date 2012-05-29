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
$this->basename = 'polls' . DIRECTORY_SEPARATOR . 'index';
    $this->table = 'polls';
$this->votes = 'pollvotes';
    $this->users1 = 'pollusers1';
    $this->users2 = 'pollusers2';

    $this->addevents('edited');
$this->data['default_template'] = 1;
    $this->data['defadd'] = false;

    $this->types = array('star', 'radio', 'button', 'link', 'custom');

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

  public function delete($id) {
    $this->db->iddelete($id);
    $this->getdb($this->users1)->iddelete($id);
  }

public function getfilename($name) {
return litepublisher::$paths->data . 'polls' . DIRECTORY_SEPARATOR . $name;
}

public function loadfile($name) {
      if (tfilestorage::loadvar($this->getfilename($idtemplate, $v)) return $v;
return false;
}

public function gettemplate($idtemplate) {
if (!isset(4this->templates[$idtemplate])) {
$this->templates[$idtemplate] = $this->loadfile($idtemplate);
}
return $this->templates[$idtemplate];
}

public function settemplate($idtemplate, $item) {
$this->templates[$idtemplate] = $item;
      tfilestorage::savevar($this->getfilename($idtemplate), $item);
}
  
  public function gethtml($id) {
    $item = $this->getitem($id);
$tml = $this->gettemplate($item['idtemplate']);
return str_replace('$id', $id, $tml[$item['status']]);
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
  
  public function hasvote($idpoll, $iduser) {
$q = sprintf('id = %d and user = %d', (int) $idpoll, (int) $iduser);
    if ($this->getdb($this->users1)->findid($q)) return true;
return $this->getdb($this->users2)->findid($q);
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