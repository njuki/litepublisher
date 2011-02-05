<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditem extends tpost {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getchildtable() {
    return 'downloaditems';
  }
  
  protected function create() {
    parent::create();
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
    'id' => 0,
    'type' => 'theme',
'downloads' => 0,
    'downloadurl'  => '',
    'authorurl'  => '',
'authorname' => '',
    'version'=> '1.00',
    'votes' => 0,
    'poll' => 0
    );
  }

public function getparenttag() {
return $this->type == 'theme' ? litepublisher::$options->downloaditem_themetag : litepublisher::$options->downloaditem_plugintag;
}

  public function settagnames($names) {
$names = trim($names);
if ($names == '') {
$this->tags = array();
return;
}
$parent = $this->getparenttag();
    $tags = ttags::instance();
    $items = array();
$list = explode(',', trim($names));
    foreach ($list as $title) {
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $items[] = $tags->add($parent, $title);
    }

    $this->tags=  $items;
  }

   public function gethead() {
    $result = parent::gethead();
$template = ttemplate::instance();
$result .= $template->getjavascript('/plugins/' . basename(dirname(__file__)) . '/downloaditem.js');
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethead();
    }
    return  $result;
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll, true);
    }
    
    $result .= parent::getcontentpage($page);
    return $result;
  }
  
  public function updatefiltered() {
parent::updatefiltered();
$this->filtered = $this->getdownloadcontent() . $this->filtered;
  }
  
  public function getdownloadcontent() {
    self::checklang();
ttheme::$vars['lang'] = tlocal::instance('downloaditem');
    ttheme::$vars['post'] = $this;
    $theme = $this->theme;
    return $theme->parse($theme->templates['custom']['downloaditem']);
  }

public function getdownloadcount() {
$lang = tlocal::instance('downloaditem');
return sprintf($lang->downloads, $this->downloads);
}
  
  public function closepoll() {
    $polls = tpolls::instance();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
  public static function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public static function checklang() {
    tlocal::loadsection('', 'download', self::getresource());
  }
  
}//class
?>