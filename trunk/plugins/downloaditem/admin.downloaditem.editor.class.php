<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemeditor extends tposteditor {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
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
    $html->addini('downloaditems', $dir . 'html.ini');
    tlocal::loadsection('', 'downloaditem', $dir);
    tlocal::loadsection('admin', 'downloaditems', $dir);
    tlocal::$data['downloaditems'] = tlocal::$data['downloaditem'] + tlocal::$data['downloaditems'];
    return parent::gethtml($name);
  }
  
  
  public function getcontent() {
$result = '';
    $this->basename = 'downloaditems';
    $downloaditem = tdownloaditem::instance($this->idpost);
    ttheme::$vars['downloaditem'] = $downloaditem;
    $args = targs::instance();
$this->getpostargs($downloaditem, $args);
    
    $html = $this->html;
    $lang = tlocal::instance('downloaditems');
    
    $types = array(
    'theme' => tlocal::$data['downloaditem']['theme'],
    'plugin' => tlocal::$data['downloaditem']['plugin']
    );
    
    $args->type = tadminhtml::array2combo($types, $downloaditem->type);

    if ($downloaditem->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
unset(ttheme::$vars['downloaditem']);
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
    $this->basename = 'downloaditems';
    $html = $this->html;
    $lang = tlocal::instance('editor');
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $downloaditem = tdownloaditem::instance((int)$id);
$this->set_post($downloaditem);
    $downloaditem->version = $version;
      $downloaditem->type = $type;
    $downloaditems = tdownloaditems::instance();
if ($downloaditem->id == 0) {
      $id = $downloaditems->add($downloaditem);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
    } else {
      $downloaditems->edit($downloaditem);
    }
    $lang = tlocal::instance('downloaditems');
    return $html->h2->successedit;
  }
  
}//class
?>