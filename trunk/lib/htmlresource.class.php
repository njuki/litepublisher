<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thtmltag {
  public $tag;
public function __construct($tag) { $this->tag = $tag; }
  public function __get($name) {
    $lang = tlocal::instance();
  return "<$this->tag>{$lang->$name}</$this->tag>\n";
  }
  
}//class

class THtmlResource  {
  public static $tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong');
  public $section;
  public $ini;
  private $map;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->ini = array();
    if (litepublisher::$options->installed) {
      $this->load('adminhtml');
      tlocal::loadlang('admin');
    } else {
      $this->loadini(litepublisher::$paths->languages . 'adminhtml.ini');
      $this->loadini(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.ini');
    }
  }
  
  public function __get($name) {
    if (in_array($name, self::$tags)) return new thtmltag($name);
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    return $s;
  }
  
  public function __call($name, $params) {
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : targs::instance();
    return $this->parsearg($s, $args);
  }
  
  public function parsearg($s, targs $args) {
    $theme = ttheme::instance();
    if (preg_match_all('/\[(area|editor|edit|checkbox|text|combo|hidden)(:|=)(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
      $admin = $theme->content->admin;
      foreach ($m as $item) {
        $type = $item[1];
        $name = $item[3];
        $varname = '$' . $name;
        //convert spec charsfor editor
        if (($type != 'checkbox') || ($name != 'combo')) {
          if (isset($args->data[$varname])) {
            $args->data[$varname] = str_replace(
            array('"', "'", '$'),
            array('&quot;', '&#39;', '&#36;'),
            htmlspecialchars($args->data[$varname]));
          } else {
            $args->data[$varname] = '';
          }
        }
        
        $tag = str_replace(array('$name', '$value'),
        array($name, $varname), $admin->$type);
        $s = str_replace($item[0], $tag, $s);
      }
    }
    
    $s = strtr($s, $args->data);
    return $theme->parse($s);
  }
  
  public function fixquote($s) {
    $s = str_replace("\\'", '\"', $s);
    $s = str_replace("'", '"', $s);
    return str_replace('\"', "'", $s);
  }
  
  public function load($name) {
    $cachefilename = tlocal::getcachefilename($name);
    if (tfiler::unserialize($cachefilename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini;
    } else {
      $v = parse_ini_file(litepublisher::$paths->languages . $name . '.ini', true);
      $this->ini = $v + $this->ini;
      tfiler::serialize($cachefilename, $v);
    }
  }
  
  public function loadini($filename) {
    if( $v = parse_ini_file($filename, true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
  public static function array2combo(array $items, $selname) {
    $result = '';
    foreach ($items as $name => $title) {
      $selected = $selname == $name ? "selected='selected'" : '';
      $result .= "<option value='$name' $selected>$title</option>\n";
    }
    return $result;
  }
  
  public function adminform($tml, targs $args) {
    $args->items = $this->parsearg($tml, $args);
    $theme = ttheme::instance();
    return $this->parsearg($theme->content->admin->form, $args);
  }
  
  public function getcheckbox($name, $value) {
    $theme = ttheme::instance();
    return str_replace(array('$name', '$value'),
    array($name, $value ? 'checked="checked"' : ''), $theme->content->admin->checkbox);
  }

public function gettable($head, $body) {
return strtr($this->ini['common']['table'], array(
'$tablehead' => $head,
'$tablebody' => $body));
}
  
public function buildtable(array $items, array $tablestruct) {
$head = '';
$body = '';
$tml = '<tr>';
foreach ($tablestruct as $elem) {
$head .= sprintf('<th lign="%s">%s</th>', $elem[0], $elem[1]);
$tml .= sprintf('<td align="%s">%s</td>', $elem[0], $elem[2]);
}
$tml .= '</tr>';

$theme = ttheme::instance();
$args = targs::instance();
foreach ($items as $id => $item) {
$args->add($item);
if (!isset($item['id'])) $args->id = $id;
$body .= $theme->parsearg($tml, $args);
}
$args->tablehead  = $head;
$args->tablebody = $body;
return $theme->parsearg($this->ini['common']['table'], $args);
}

  public function confirmdelete($id, $adminurl, $mesg) {
    $args = targs::instance();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;
    return $this->confirmform($args);
  }

}//class

class tautoform {
const editor = 'editor';
const text = 'text';
const checkbox = 'checkbox';
const hidden = 'hidden';

public $obj;
public $props;
public $section;
public $_title;

  public static function instance() {
    return getinstance(__class__);
  }
  
public function __create(tdata $obj, $section, $titleindex) {
$this->obj = $obj;
$this->section = $section;
$this->props = array();
$lang = tlocal::instance($section);
$this->_title = $lang->$titleindex;
}

public function __set($name, $value) {
$this->props[] = array(
'obj' => $this->obj,
'propname' => $name,
'type' => $value
);
}

public function __get($name) {
if (isset($this->obj->$name)) {
return array(
'obj' => $this->obj,
'propname' => $name
);
}
//tlogsubsystem::error(sprintf('The property %s not found in class %s', $name, get_class($this->obj));
}

public function __call($name, $args) {
if (isset($this->obj->$name)) {
return array(
'obj' => $this->obj,
'propname' => $name,
'type' => $args[0]
);
}
}

public function add() {
    $a = func_get_args();
foreach ($a as $prop) {
$this->addprop($prop);
}
}

p
ublic function addprop(array $prop) {
if (isset($prop['type'])) {
$type = $prop['type'];
} else {
$type = 'text';
$value = $prop['obj']->{$prop['propname']};
if (is_bool($value)) {
$type = 'checkbox';
}strpos($value, "\n")) {
$type = 'editor'; elseif (
}
}

$this->props[] = array(
'obj' => $prop['obj'],
'propname' => $prop['propname'],
'type' => $type,
'title' => isset($prop['title']) ? $prop['title'] : ''
);
return count($this->props) - 1;
}

public function getcont() {
$items = '';
$theme = ttheme::instance();
      $admin = $theme->content->admin;
foreach ($this->props as $prop) {
$value = $prop['obj']->{$prop['propname']};
switch ($prop['type']) {
case 'text':
case 'editor':
$value = str_replace(
            array('"', "'", '$'),
            array('&quot;', '&#39;', '&#36;'),
            htmlspecialchars($value));
break;

case 'checkbox':
$value = $value ? 'checked="checked"' : '';
break;

case 'combo':
$value = THtmlResource  ::array2combo($prop['items'], $value);
break;
}

$items .= strtr($admin->{$prop['type']}, array(
'$lang.$name' => empty($prop['title']) ? $lang->{$prop['propname']} : $prop['title'],
'$name' => $prop['propname'],
'$value' => $value
));
}

$args = targs::instance();
$args->formtitle = $this->_title;
    $args->items = $items;
    return $theme->parsearg($theme->content->admin->form, $args);
}

public function processform() {
foreach ($this->props as $prop) $prop['obj']->lock();

foreach ($this->props as $prop) {
if (isset($_POST[$name])) {
$value = $_POST[$name];
if ($value == 'checked') $value = true;
} else {
$value = false;
}
$prop['obj']->$name = $value;
}

foreach ($this->props as $prop) $prop['obj']->unlock();
}

}//class
?>