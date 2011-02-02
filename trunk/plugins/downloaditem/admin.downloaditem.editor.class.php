<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemeteditor extends tposteditor {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $result .= '
    <script type="text/javascript">
      inittabs("#contenttabs");
    </script>';
    return $result;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      tlocal::loadsection('admin', 'downloaditems', dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
      return tlocal::$data['downloaditems']['editor'];
    }
  }
  
  public function gethtml($name = '') {
    $html = tadminhtml::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $html->addini('tickets', $dir . 'html.ini');
    //$html->section = 'tickets';
    tlocal::loadsection('', 'ticket', $dir);
    tlocal::loadsection('admin', 'tickets', $dir);
    tlocal::$data['tickets'] = tlocal::$data['ticket'] + tlocal::$data['tickets'];
    return parent::gethtml($name);
  }
  
  public function getcontent() {
    $this->basename = 'downloaditems';
    $downloaditem = tdownloaditem::instance($this->idpost);
    ttheme::$vars['downloaditem'] = $downloaditem;
    $args = targs::instance();
    $args->id = $this->idpost;
    $args->title = $downloaditem->title;
    $args->categories = $this->getpostcategories($downloaditem);
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$downloaditem->id&get");
    $ajaxeditor = tajaxposteditor ::instance();
    $args->raw = $ajaxeditor->geteditor('raw', $downloaditem->rawcontent, true);
    
    $html = $this->html;
    $lang = tlocal::instance('downloaditems');
    
    $types = array(
    'theme' => tlocal::$data['downloaditem']['theme'],
    'plugin' => tlocal::$data['downloaditem']['plugin']
    );
    
    $args->typecombo= $html->array2combo($types, $downloaditem->type);
    $args->typedisabled = $downloaditem->id == 0 ? '' : "disabled = 'disabled'";
    
    if ($downloaditem->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    /*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return;
    */
    extract($_POST, EXTR_SKIP);
    $downloaditems = tdownloaditems::instance();
    $this->basename = 'downloaditems';
    $html = $this->html;
    
    if (empty($title)) return $html->h2->emptytitle;
    $downloaditem = tdownloaditem::instance((int)$id);
$this->set_post($downloaditem);
    $downloaditem->version = $version;
    if (litepublisher::$options->group != 'downloaditem') $downloaditem->state = $state;
    if ($id == 0) {
      $downloaditem->status = $newstatus;
      $downloaditem->type = $type;
      $downloaditem->closed = time();
      $id = $downloaditems->add($downloaditem);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
      if (litepublisher::$options->group == 'downloaditem') {
        $users =tusers::instance();
        $user = $users->getitem(litepublisher::$options->user);
        $comusers = tcomusers::instance();
        $uid = $comusers->add($user['name'], $user['email'], $user['url'], '');
        $comusers->setvalue($uid, 'cookie', $user['cookie']);
        $subscribers = tsubscribers::instance();
        //$subscribers->update($id, $uid, true);
        $subscribers->add($id, $uid);
      }
    } else {
      $downloaditems->edit($downloaditem);
    }
    
    return $html->h2->successedit;
  }
  
}//class
?>