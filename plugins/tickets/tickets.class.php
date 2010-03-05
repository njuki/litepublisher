<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttickets extends tplugin implements iposts {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'tickets';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'tickets';
$this->data['infottml'] = '';
  }

  public function add(tpost $post) {
$post->status = 'draft';
// just send notify to admin
}

  public function edit(tpost $post) { }
  public function delete($id) {}

public function checkadminlang() {
if (!isset(tlocal::$data['tickets'])) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
      $v = parse_ini_file($dir . litepublisher::$options->language . '.admin.ini');
    tlocal::$data = $v + tlocal::$data ;
if (!isset(tlocal::$data['ticket'])) {
      $v = parse_ini_file($dir . litepublisher::$options->language . '.ini');
    tlocal::$data = $v + tlocal::$data ;
}
      tfiler::serialize(litepublisher::$paths->languages . 'admin' . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . 'admin' . litepublisher::$options->language . '.js');
    }
}

public function checklang() {
if (!isset(tlocal::$data['ticket'])) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
      $v = parse_ini_file($dir . litepublisher::$options->language . '.ini');
    tlocal::$data = $v + tlocal::$data ;
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
}

public function aftercontent($id, &$content) {
if (litepublisher::$urlmap->page > 1) return;
$this->checklang();
$lang = tlocal::instance('ticket');
$post = tpost::instance($id);
$ticket = $post->ticket;
$args = targs::instance();
foreach (array('type', 'state', 'prio') as $prop) {
$value = $ticket->$prop;
$args->$prop = $lang->$value;
}
$args->reproduced = $ticket->reproduced ? $lang->yesword : $lang->noword;
if ($ticket.assignto <= 1) {
$profile = tprofile::instance();
$args->assignto = $profile->nick;
} else {
$users = tusers::instance();
$account = $users->getaccount($ticket->assignto);
$args->assignto = $this->$account['name'];
}

ttheme::$vars['ticket'] = $ticket;
$theme = ttheme::instance();
$info = $theme->parsearg($this->infotml, $args);
if (dbversion && ($ticket.poll > 1)) {
$polls = tpolls::instance();
$info .= $polls->gethtml($ticket->poll);
}
$content = $info . $content;
$code = str_replace(
          array('"', "'", '$'),
          array('&quot;', '&#39;', '&#36;'),
          htmlspecialchars($post->code));
if ($code != '') $content .= "\n<code><pre>\n$code\n</pre></code>\n";
}

public function deletedeleted($deleted) {
$idpolls = litepublisher::$db->res2id(litepublisher::$db->query("select poll from $this->thistable where id in $deleted and poll > 0"));
$this->db->delete("id in $deleted");
if (count($idpoll) > 0) {
$polls = tpolls::instance();
$pols->deletedeleted($idpolls);
}
}

  }//class
?>