<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
//menu.admin.class.php
class tadminmenus extends tmenus {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'adminmenu';
    $this->addevents('onexclude');
    $this->data['heads'] = '';
    tadminmenu::$ownerprops = array_merge(tadminmenu::$ownerprops, array('name', 'group'));
  }
  
  public function settitle($id, $title) {
    if ($id && isset($this->items[$id])) {
      $this->items[$id]['title'] = $title;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
  }
  
  public function getadmintitle($name) {
    $lang = tlocal::i();
    $ini = &$lang->ini;
    if (isset($ini[$name]['title'])) {
      return $ini[$name]['title'];
    } elseif (isset($ini[$lang->section][$name])) {
      return $ini[$lang->section][$name];
    } elseif (isset($ini['names'][$name])) {
      return $ini['names'][$name];
    } elseif (isset($ini['default'][$name])) {
      return $ini['default'][$name];
    } elseif (isset($ini['common'][$name])) {
      return $ini['common'][$name];
    } else {
      return $name;
    }
  }
  
  public function createitem($parent, $name, $group, $class) {
    $title = $this->getadmintitle($name);
    $url = $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
    return $this->additem(array(
    'parent' => $parent,
    'url' => $url,
    'title' => $title,
    'name' => $name,
    'class' => $class,
    'group' => $group
    ));
  }
  
  public function hasright($group) {
    $groups = tusergroups::i();
    return $groups->hasright(litepublisher::$options->group, $group);
  }
  
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      foreach ($this->tree as $iditem => $items) {
        if ($this->hasright($this->items[$iditem]['group']))
        $result[] = $iditem;
      }
      return $result;
    }
    
    $parents = array($id);
    $parent = $this->items[$id]['parent'];
    while ($parent != 0) {
      array_unshift ($parents, $parent);
      $parent = $this->items[$parent]['parent'];
    }
    
    $tree = $this->tree;
    foreach ($parents as $parent) {
      foreach ($tree as $iditem => $items) {
        if ($iditem == $parent) {
          $tree = $items;
          break;
        }
      }
    }
    return array_keys($tree);
  }
  
  public function exclude($id) {
    if (!$this->hasright($this->items[$id]['group'])) return  true;
    return $this->onexclude($id);
  }
  
}//class

class tadminmenu  extends tmenu {
  public $arg;
  
  public static function getinstancename() {
    return 'adminmenu';
  }
  
  public static function getowner() {
    return tadminmenus::i();
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
  }
  
public function load() { return true; }
public function save() { return true; }
  
  public function gethead() {
    return tadminmenus::i()->heads;
  }
  
  public function getidview() {
    return tviews::i()->defaults['admin'];
  }
  
  public static function auth($group) {
    if (litepublisher::$options->cookieenabled) {
      if ($s = tguard::checkattack()) return $s;
      if (!litepublisher::$options->user) {
        return litepublisher::$urlmap->redir('/admin/login/' . litepublisher::$site->q . 'backurl=' . urlencode(litepublisher::$urlmap->url));
      }
    }else {
      $auth = tauthdigest::i();
      if (!$auth->Auth())  return $auth->headers();
    }
    
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::i();
      if (!$groups->hasright(litepublisher::$options->group, $group)) return 403;
    }
  }
  
  public function request($id) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
    
    if (is_null($id)) $id = $this->owner->class2id(get_class($this));
    $this->data['id'] = (int)$id;
    if ($id > 0) {
      $this->basename =  $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
    }
    
    if ($s = self::auth($this->group)) return $s;
    tlocal::usefile('admin');
    $this->arg = litepublisher::$urlmap->argtree;
    if ($s = $this->canrequest()) return $s;
    $this->doprocessform();
  }
  
public function canrequest() { }
  
  protected function doprocessform() {
    if (tguard::post()) {
      litepublisher::$urlmap->clearcache();
    }
    return parent::doprocessform();
  }
  
  public function getcont() {
    if (litepublisher::$options->admincache) {
      $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
      $cachefile = litepublisher::$paths->cache . 'adminmenu.' . litepublisher::$options->user . '.' .md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
      if (file_exists($cachefile)) return file_get_contents($cachefile);
      $result = parent::getcont();
      file_put_contents($cachefile, $result);
      @chmod($filename, 0666);
      return $result;
    } else {
      return parent::getcont();
    }
  }
  
  public static function idget() {
    return (int) tadminhtml::getparam('id', 0);
  }
  
  public function getaction() {
    return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
  }
  
  public function gethtml($name = '') {
    $result = tadminhtml::i();
    if ($name == '') $name = $this->basename;
    if (!isset($result->ini[$name])) {
      $name = $this->owner->items[$this->parent]['name'];
    }
    
    $result->section = $name;
    $lang = tlocal::i($name);
    return $result;
  }
  
  public function getlang() {
    return tlocal::i($this->name);
  }
  
  public function getconfirmed() {
    return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function getnotfound() {
    return $this->html->h4->notfound;
  }
  
  public function getadminurl() {
    return litepublisher::$site->url .$this->url . litepublisher::$site->q . 'id';
  }
  
  public function getfrom($perpage, $count) {
    if (litepublisher::$urlmap->page <= 1) return 0;
    return min($count, (litepublisher::$urlmap->page - 1) * $perpage);
  }
  
}//class

class tauthor_rights extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
    $this->basename = 'authorrights';
  }
  
}//class

//htmlresource.class.php
class thtmltag {
  public $tag;
  
public function __construct($tag) { $this->tag = $tag; }
  public function __get($name) {
    return sprintf('<%1$s>%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

class tadminhtml {
  public static $tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong');
  public $section;
  public $ini;
  private $map;
  
  public static function i() {
    $self = getinstance(__class__);
    if (count($self->ini) == 0) $self->load();
    return $self;
  }
  
  public static function getinstance($section) {
    $self = self::i();
    $self->section = $section;
    tlocal::i($section);
    return $self;
  }
  
  public function __construct() {
    $this->ini = array();
    tlocal::usefile('admin');
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name]))  {
      return $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      return $this->ini['common'][$name];
    } elseif (in_array($name, self::$tags)) {
      return new thtmltag($name);
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
  }
  
  public function __call($name, $params) {
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } elseif (in_array($name, self::$tags)) {
      return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : targs::i();
    return $this->parsearg($s, $args);
  }
  
  public function parsearg($s, targs $args) {
    if (!is_string($s)) $s = (string) $s;
    $theme = ttheme::i();
    // parse tags [form] .. [/form]
    $form = $theme->templates['content.admin.form'];
    if (is_int($i = strpos($s, '[form]'))) {
      $replace = substr($form, 0, strpos($form, '$items'));
      $s = substr_replace($s, $replace, $i, strlen('[form]'));
    }
    
    if ($i = strpos($s, '[/form]')) {
      $replace = substr($form, strrpos($form, '$items') + strlen('$items'));
      $s = substr_replace($s, $replace, $i, strlen('[/form]'));
    }
    
    if (preg_match_all('/\[(editor|checkbox|text|password|combo|hidden)(:|=)(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $type = $item[1];
        $name = $item[3];
        $varname = '$' . $name;
        //convert spec charsfor editor
        if (!(($type == 'checkbox') || ($type == 'combo'))) {
          if (isset($args->data[$varname])) {
            $args->data[$varname] = self::specchars($args->data[$varname]);
          } else {
            $args->data[$varname] = '';
          }
        }
        $tag = strtr($theme->templates["content.admin.$type"], array(
        '$name' => $name,
        '$value' =>$varname
        ));
        $s = str_replace($item[0], $tag, $s);
      }
    }
    $s = strtr($s, $args->data);
    return $theme->parse($s);
  }
  
  public static function specchars($s) {
    return strtr(            htmlspecialchars($s), array(
    '"' => '&quot;',
    "'" =>'&#39;',
    '$' => '&#36;',
    '%' => '&#37;',
    '_' => '&#95;'
    ));
  }
  
  public function fixquote($s) {
    $s = str_replace("\\'", '\"', $s);
    $s = str_replace("'", '"', $s);
    return str_replace('\"', "'", $s);
  }
  
  public function load() {
    $filename = tlocal::getcachedir() . 'adminhtml';
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini;
    } else {
      $merger = tlocalmerger::i();
      $merger->parsehtml();
    }
  }
  
  public function loadinstall() {
    if (isset($this->ini['installation'])) return;
    tlocal::usefile('install');
    if( $v = parse_ini_file(litepublisher::$paths->languages . 'install.ini', true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
  public static function getparam($name, $default) {
    return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
  }
  
  public static function idparam() {
    return (int) self::getparam('id', 0);
  }
  
  public static function getadminlink($path, $params) {
    return litepublisher::$site->url . $path . litepublisher::$site->q . $params;
  }
  
  public static function array2combo(array $items, $selected) {
    $result = '';
    foreach ($items as $i => $title) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', self::specchars($title));
    }
    return $result;
  }
  
  public static function getcombobox($name, array $items, $selected) {
    return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name,
    self::array2combo($items, $selected));
  }
  
  public function adminform($tml, targs $args) {
    $args->items = $this->parsearg($tml, $args);
    return $this->parsearg(ttheme::i()->templates['content.admin.form'], $args);
  }
  
  public function getcheckbox($name, $value) {
    return $this->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
  }
  
  public function getradioitems($name, array $items, $selected) {
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->templates['content.admin.radioitems'];
    foreach ($items as $index => $value) {
      $result .= strtr($tml, array(
      '$index' => $index,
      '$checked' => $value == $selected ? 'checked="checked"' : '',
      '$name' => $name,
      '$value' => self::specchars($value)
      ));
    }
    return $result;
  }
  
  public function getinput($type, $name, $value, $title) {
    $theme = ttheme::i();
    return strtr($theme->templates['content.admin.' . $type], array(
    '$lang.$name' => $title,
    '$name' => $name,
    '$value' => $value
    ));
  }
  
  public function getsubmit($name) {
    return strtr(ttheme::i()->templates['content.admin.submit'], array(
    '$lang.$name' => tlocal::i()->$name,
    '$name' => $name,
    ));
  }
  
  public function getedit($name, $value, $title) {
    return $this->getinput('text', $name, $value, $title);
  }
  
  public function getcombo($name, $value, $title) {
    return $this->getinput('combo', $name, $value, $title);
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
      $head .= sprintf('<th align="%s">%s</th>', $elem[0], $elem[1]);
      $tml .= sprintf('<td align="%s">%s</td>', $elem[0], $elem[2]);
    }
    $tml .= '</tr>';
    
    $theme = ttheme::i();
    $args = targs::i();
    foreach ($items as $id => $item) {
      $args->add($item);
      if (!isset($item['id'])) $args->id = $id;
      $body .= $theme->parsearg($tml, $args);
    }
    $args->tablehead  = $head;
    $args->tablebody = $body;
    return $theme->parsearg($this->ini['common']['table'], $args);
  }
  
  public function items2table($owner, array $items, array $struct) {
    $head = '';
    $body = '';
    $tml = '<tr>';
    foreach ($struct as $elem) {
      $head .= sprintf('<th align="%s">%s</th>', $elem[0], $elem[1]);
      $tml .= sprintf('<td align="%s">%s</td>', $elem[0], $elem[2]);
    }
    $tml .= '</tr>';
    
    $theme = ttheme::i();
    $args = new targs();
    foreach ($items as $id) {
      $item = $owner->getitem($id);
      $args->add($item);
      $args->id = $id;
      $body .= $theme->parsearg($tml, $args);
    }
    $args->tablehead  = $head;
    $args->tablebody = $body;
    return $theme->parsearg($this->ini['common']['table'], $args);
  }
  
  public function get_table_checkbox($name) {
    return array('center', $this->invertcheckbox, str_replace('$checkboxname', $name, $this->checkbox));
  }
  
  public function get_table_item($name) {
    return array('left', tlocal::i()->$name, "\$$name");
  }
  
  public function get_table_link($action, $adminurl) {
    return array('left', tlocal::i()->$action, strtr($this->actionlink , array(
    '$action' => $action,
    '$lang.action' => tlocal::i()->$action,
    '$adminurl' => $adminurl
    )));
  }
  
  public function confirmdelete($id, $adminurl, $mesg) {
    $args = targs::i();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;
    return $this->confirmform($args);
  }
  
  public function confirm_delete($owner, $adminurl) {
    $id = (int) self::getparam('id', 0);
    if (!$owner->itemexists($id)) return $this->h4->notfound;
    if  (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
      $owner->delete($id);
      return $this->h4->successdeleted;
    } else {
      $args = new targs();
      $args->id = $id;
      $args->adminurl = $adminurl;
      $args->action = 'delete';
      $args->confirm = tlocal::i()->confirmdelete;
      return $this->confirmform($args);
    }
  }
  
  public static function check2array($prefix) {
    $result = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, $prefix)) {
        $result[] = is_numeric($value) ? (int) $value : $value;
      }
    }
    return $result;
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
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct(tdata $obj, $section, $titleindex) {
    $this->obj = $obj;
    $this->section = $section;
    $this->props = array();
    $lang = tlocal::i($section);
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
      $result = array(
      'obj' => $this->obj,
      'propname' => $name,
      'type' => $args[0]
      );
      if (($result['type'] == 'combo') && isset($args[1]))  $result['items'] = $args[1];
      return $result;
    }
  }
  
  public function add() {
    $a = func_get_args();
    foreach ($a as $prop) {
      $this->addprop($prop);
    }
  }
  
  public function addsingle($obj, $propname, $type) {
    return $this->addprop(array(
    'obj' => $obj,
    'propname' => $propname,
    'type' => $type
    ));
  }
  
  public function addeditor($obj, $propname) {
    return $this->addsingle($obj, $propname, 'editor');
  }
  
  public function addprop(array $prop) {
    if (isset($prop['type'])) {
      $type = $prop['type'];
    } else {
      $type = 'text';
    $value = $prop['obj']->{$prop['propname']};
      if (is_bool($value)) {
        $type = 'checkbox';
      } elseif(strpos($value, "\n")) {
        $type = 'editor';
      }
    }
    
    $item = array(
    'obj' => $prop['obj'],
    'propname' => $prop['propname'],
    'type' => $type,
    'title' => isset($prop['title']) ? $prop['title'] : ''
    );
    if (($type == 'combo') && isset($prop['items'])) $item['items'] = $prop['items'];
    $this->props[] = $item;
    return count($this->props) - 1;
  }
  
  public function getcontent() {
    $result = '';
    $lang = tlocal::i();
    $theme = ttheme::i();
    
    foreach ($this->props as $prop) {
    $value = $prop['obj']->{$prop['propname']};
      switch ($prop['type']) {
        case 'text':
        case 'editor':
        $value = tadminhtml::specchars($value);
        break;
        
        case 'checkbox':
        $value = $value ? 'checked="checked"' : '';
        break;
        
        case 'combo':
        $value = tadminhtml  ::array2combo($prop['items'], $value);
        break;
      }
      
      $result .= strtr($theme->templates['content.admin.' . $prop['type']], array(
    '$lang.$name' => empty($prop['title']) ? $lang->{$prop['propname']} : $prop['title'],
      '$name' => $prop['propname'],
      '$value' => $value
      ));
    }
    return $result;
  }
  
  public function getform() {
    $args = targs::i();
    $args->formtitle = $this->_title;
    $args->items = $this->getcontent();
    $theme = ttheme::i();
    return $theme->parsearg($theme->templates['content.admin.form'], $args);
  }
  
  public function processform() {
    foreach ($this->props as $prop) {
      if (method_exists($prop['obj'], 'lock')) $prop['obj']->lock();
    }
    
    foreach ($this->props as $prop) {
      $name = $prop['propname'];
      if (isset($_POST[$name])) {
        $value = trim($_POST[$name]);
        if ($prop['type'] == 'checkbox') $value = true;
      } else {
        $value = false;
      }
      $prop['obj']->$name = $value;
    }
    
    foreach ($this->props as $prop) {
      if (method_exists($prop['obj'], 'unlock')) $prop['obj']->unlock();
    }
  }
  
}//class

class ttablecolumns {
  public $style;
  public $head;
  public $checkboxes;
  public $checkbox_tml;
  public $item;
  public $changed_hidden;
  public $index;
  
  public function __construct() {
    $this->index = 0;
    $this->style = '';
    $this->checkboxes = array();
    $this->checkbox_tml = '<input type="checkbox" name="checkbox-showcolumn-%1$d" value="%1$d" %2$s />
    <label for="checkbox-showcolumn-%1$d"><strong>%3$s</strong></label>';
    $this->head = '';
    $this->body = '';
    $this->changed_hidden = 'changed_hidden';
  }
  
  public function addcolumns(array $columns) {
    foreach ($columns as $column) {
      list($tml, $title, $align, $show) = $column;
      $this->add($tml, $title, $align, $show);
    }
  }
  
  public function add($tml, $title, $align, $show) {
    $class = 'col_' . ++$this->index;
    //if (isset($_POST[$this->changed_hidden])) $show  = isset($_POST["checkbox-showcolumn-$this->index"]);
    $display = $show ? 'block' : 'none';
  $this->style .= ".$class { text-align: $align; display: $display; }\n";
    $this->checkboxes[]=  sprintf($this->checkbox_tml, $this->index, $show ? 'checked="checked"' : '', $title);
    $this->head .= sprintf('<th class="%s">%s</th>', $class, $title);
    $this->body .= sprintf('<td class="%s">%s</td>', $class, $tml);
    return $this->index;
  }
  
  public function build($body, $buttons) {
    $args = targs::i();
    $args->style = $this->style;
    $args->checkboxes = implode("\n", $this->checkboxes);
    $args->head = $this->head;
    $args->body = $body;
    $args->buttons = $buttons;
    $tml = file_get_contents(litepublisher::$paths->languages . 'tablecolumns.ini');
    $theme = ttheme::i();
    return $theme->parsearg($tml, $args);
  }
  
}//class

class tuitabs {
  public $head;
  public $body;
  public $tabs;
  private static $index = 0;
  private $items;
  
  public function __construct() {
    self::$index++;
    $this->items = array();
    $this->head = '<li><a href="#tab-' . self::$index. '-%d"><span>%s</span></a></li>';
    $this->body = '<div id="tab-' . self::$index . '-%d">%s</div>';
    $this->tabs = '<div id="tabs-' . self::$index . '" rel="tabs">
    <ul>%s</ul>
    %s
    </div>';
  }
  
  public function get() {
    $head= '';
    $body = '';
    foreach ($this->items as $i => $item) {
      $head .= sprintf($this->head, $i, $item['title']);
      $body .= sprintf($this->body, $i, $item['body']);
    }
    return sprintf($this->tabs, $head, $body);
  }
  
  public function add($title, $body) {
    $this->items[] = array(
    'title' => $title,
    'body' => $body
    );
  }
  
  public static function gethead() {
    return ttemplate::i()->getready('$($("div[rel=\'tabs\']").get().reverse()).tabs()');
  }
  
}//class

//admin.posteditor.ajax.class.php
class tajaxposteditor  extends tevents {
  public $idpost;
  private $isauthor;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'ajaxposteditor';
    $this->addevents('onhead', 'oneditor');
    $this->data['head'] = '';
    $this->data['visual'] = '';
    //'/plugins/tiny_mce/init.js';
    //'/plugins/ckeditor/init.js';
    $this->data['ajaxvisual'] = true;
  }
  
  public function dogethead($head) {
    $template = ttemplate::i();
    $template->ltoptions['upload_button_text'] = tlocal::i()->upload;
    $head .= $this->head;
    if ($this->visual) {
      if ($this->ajaxvisual) {
        $head .= $template->getready('$("a[rel~=\'loadvisual\']").one("click", function() {
          $("#loadvisual").remove();
          $.getScript("' . litepublisher::$site->files . $this->visual . '");
          return false;
        });');
      } else {
        $head .= $template->getjavascript($this->visual);
      }
    }
    
    $this->callevent('onhead', array(&$head));
    return $head;
  }
  
  protected static function error403() {
    return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
  }
  
  public function getviewicon($idview, $icon) {
    $result = tadminviews::getcomboview($idview);
    if ($icons = tadminicons::getradio($icon)) {
      $html = tadminhtml ::i();
      if ($html->section == '') $html->section = 'editor';
      $result .= $html->h2->icons;
      $result .= $icons;
    }
    return $result;
  }
  
  public static function auth() {
    if (!litepublisher::$options->cookieenabled) return self::error403();
    if (!litepublisher::$options->user) return self::error403();
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::i();
      if (!$groups->hasright(litepublisher::$options->group, 'author')) return self::error403();
    }
  }
  
  public function request($arg) {
    $this->cache = false;
    //tfiler::log(var_export($_GET, true) . var_export($_POST, true) . var_export($_FILES, true));
    if (isset($_GET['get']) && ($_GET['get'] == 'upload')) {
      if (empty($_POST['litepubl_user'])) return self::error403();
      if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
        return "<?php
        header('Allow: POST');
        header('HTTP/1.1 405 Method Not Allowed', true, 405);
        header('Content-Type: text/plain');
        ?>";
      }
      $_COOKIE['litepubl_user'] = $_POST['litepubl_user'];
      $_COOKIE['litepubl_user_id'] = $_POST['litepubl_user_id'];
    }
    if ($err = self::auth()) return $err;
    $this->idpost = tadminhtml::idparam();
    $this->isauthor = 'author' == litepublisher::$options->group;
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return self::error403();
      $groupname = litepublisher::$options->group;
      if ($groupname != 'admin') {
        $groups = tusergroups::i();
        if (!$groups->hasright($groupname, 'editor') and  $groups->hasright($groupname, 'author')) {
          $this->isauthor = true;
          $post = tpost::i($this->idpost);
          if (litepublisher::$options->user != $post->author) return self::error403();
        }
      }
    }
    return $this->getcontent();
  }
  
  private function getfiletemplates($id, $idpost, $li_id) {
    $replace = array(
    //'<li>' => sprintf('<li><input type="checkbox" name="%1$s" id="%1$s" value="$id">', $li_id),
    '$id'=> $id,
    '$post.id'=> $idpost
    );
    
    $checkbox = sprintf('><input type="checkbox" name="%1$s" id="%1$s" value="$id" />', $li_id);
    
    $theme = ttheme::i();
    $types = $theme->reg('/^content\.post\.filelist/');
    $a = array();
    foreach ($types as $name => $val) {
      $val = strtr($val, $replace);
      $name = substr($name, strrpos($name, '.') + 1);
      if ($name == 'filelist') {
        $name = '';
      } elseif (substr($name, -1)  != 's') {
        // chicks if not an items
        $val =substr_replace($val, $checkbox, strpos($val, '>'), 1);
      }
      $a[$name] = $val;
    }
    return new tarray2prop ($a);
  }
  
  public function getcontent() {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->section = 'editor';
    $lang = tlocal::i('editor');
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    
    switch ($_GET['get']) {
      case 'tags':
      $result = $html->getedit('tags', $post->tagnames, $lang->tags);
      $lang->section = 'editor';
      $result .= $html->h4->addtags;
      $items = array();
      $tags = $post->factory->tags;
      $list = $tags->getsorted(-1, 'name', 0);
      foreach ($list as $id ) {
        $items[] = '<a href="" rel="tagtopost">' . $tags->items[$id]['title'] . "</a>";
      }
      $result .= sprintf('<p>%s</p>', implode(', ', $items));
      break;
      
      case 'posted':
      $args = targs::i();
      $args->date = $post->posted != 0 ?date('d.m.Y', $post->posted) : '';
      $args->time  = $post->posted != 0 ?date('H:i', $post->posted) : '';
      $result = $html->datepicker($args);
      break;
      
      case 'status':
      $args = new targs();
      if (dbversion) {
        $args->comstatus= tadminhtml::array2combo(array(
        'closed' => $lang->closed,
        'reg' => $lang->reg,
        'guest' => $lang->guest,
        'comuser' => $lang->comuser
        ), $post->comstatus);
      }
      
      $args->pingenabled = $post->pingenabled;
      $args->status= tadminhtml::array2combo(array(
      'published' => $lang->published,
      'draft' => $lang->draft
      ), $post->status);
      
      $args->perms = tadminperms::getcombo($post->idperm);
      $args->password = $post->password;
      $result = $html->parsearg(
      '[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>',
      $args);
      
      break;
      
      case 'view':
      $result = $this->getviewicon($post->idview, $post->icon);
      break;
      
      case 'seo':
      $form = new tautoform($post, 'editor', 'editor');
      $form->add($form->url, $form->title2, $form->keywords, $form->description);
      $result = $form->getcontent();
      $result .= tadminhtml::i()->getinput('editor', 'head', $post->data['head'], tlocal::i()->head);
      break;
      
      case 'files':
      $args = targs::i();
      $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
      $files = $post->factory->files;
      if (litepublisher::$options->show_file_perm) {
        $args->fileperm = tadminperms::getcombo(0, 'idperm_upload');
      } else {
        $args->fileperm = '';
      }
      if (count($post->files) == 0) {
        $args->currentfiles = '<ul></ul>';
      } else {
        $templates = $this->getfiletemplates('curfile-$id', 'curpost-$post.id', 'currentfile-$id');
        $args->currentfiles = $files->getlist($post->files, $templates);
      }
      
      if (dbversion) {
        $sql = "parent =0 and media <> 'icon'";
        $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
        $count = $files->db->getcount($sql);
      } else {
        $list= array();
        $uid = litepublisher::$options->user;
        foreach ($files->items as $id => $item) {
          if (($item['parent'] != 0) || ($item['media'] == 'icon')) continue;
          if ($uid > 1 && $uid != $item['author']) continue;
          $list[] = $id;
        }
        $count = count($list);
      }
      
      $pages = '';
      $perpage = 10;
      $count = ceil($count/$perpage);
      for ($i =1; $i <= $count; $i++) {
        $args->index = $i;
        $pages .= $html->pageindex($args);
      }
      
      $args->pages = $pages;
      $args->files = implode(',', $post->files);
      $result = $html->browser($args);
      break;
      
      case 'filepage':
      $page = tadminhtml::getparam('page', 1);
      $page = max(1, $page);
      
      $perpage = 10;
      $files = tfiles::i();
      if (dbversion) {
        $sql = "parent =0 and media <> 'icon'";
        $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
        $count = $files->db->getcount($sql);
        $pagescount = ceil($count/$perpage);
        $page = min($page, $pagescount);
        $from = ($page -1)  * $perpage;
        $list = $files->select($sql, " order by posted desc limit $from, $perpage");
        if (!$list) $list = array();
      } else {
        $list= array();
        $uid = litepublisher::$options->user;
        foreach ($files->items as $id => $item) {
          if (($item['parent'] != 0) || ($item['media'] == 'icon')) continue;
          if ($uid > 1 && $uid != $item['author']) continue;
          $list[] = $id;
        }
        $count = count($list);
        $pagescount = ceil($count/$perpage);
        $page = min($page, $pagescount);
        $from = ($page -1)  * $perpage;
        $list = array_slice($list, $from, $perpage);
      }
      
      if (count($list) == 0) return '';
      
      $args = targs::i();
      $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
      $args->page = $page;
      $templates = $this->getfiletemplates('pagefile-$id', 'pagepost-$post.id', 'itemfilepage-$id');
      $files = tfiles::i();
      $result = $files->getlist($list, $templates);
      $result .= $html->page($args);
      break;
      
      case 'upload':
      if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) ||
      $_FILES['Filedata']['error'] != 0) return self::error403();
      if ($this->isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
      
      $parser = tmediaparser::i();
      $id = $parser->uploadfile($_FILES['Filedata']['name'], $_FILES['Filedata']['tmp_name'], '', '', '', false);
      if (isset($_POST['idperm'])) {
        $idperm = (int) $_POST['idperm'];
        if ($idperm > 0) tprivatefiles::i()->setperm($id, (int) $_POST['idperm']);
      }
      $templates = $this->getfiletemplates('uploaded-$id', 'new-post-$post.id', 'newfile-$id');
      $files = tfiles::i();
      $result = $files->getlist(array($id), $templates);
      break;
      
      case 'contenttabs':
      $args = targs::i();
      $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
      $result = $html->contenttabs($args);
      break;
      
      case 'excerpt':
      $result = $this->geteditor('excerpt', $post->excerpt, false);
      break;
      
      case 'rss':
      $result = $this->geteditor('rss', $post->rss, false);
      break;
      
      case 'more':
      $result = $html->getedit('more', $post->moretitle, $lang->more);
      break;
      
      case 'filtered':
      $result = $this->geteditor('filtered', $post->filtered, false);
      break;
      
      case 'upd':
      $result = $this->geteditor('upd', '', false);
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    //tfiler::log($result);
    return turlmap::htmlheader(false) . $result;
  }
  
  public function geteditor($name, $value, $visual) {
    $html = tadminhtml ::i();
    $hsect = $html->section;
    $html->section = 'editor';
    $lang = tlocal::i();
    $lsect = $lang->section;
    $lang->section = 'editor';
    $title = $lang->$name;
    if ($visual && $this->ajaxvisual && $this->visual) $title .= $html->loadvisual();
    $result = $html->getinput('editor', $name, tadminhtml::specchars($value), $title);
    $lang->section = $lsect;
    $html->section = $hsect;
    return $result;
  }
  
  public function getraweditor($value) {
    $html = tadminhtml ::i();
    if ($html->section == '') $html->section = 'editor';
    $lang = tlocal::i();
    if ($lang->section == '') $lang->section = 'editor';
    $title = $lang->raw;
    if ($this->ajaxvisual && $this->visual) $title .= $html->loadvisual();
    $title .= $html->loadcontenttabs();
    return $html->getinput('editor', 'raw', tadminhtml::specchars($value), $title);
  }
  
}//class

//admin.posteditor.class.php
class tposteditor extends tadminmenu {
  public $idpost;
  private $isauthor;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    
    $template = ttemplate::i();
    $template->ltoptions['idpost'] = $this->idget();
    $template->ltoptions['lang'] = litepublisher::$options->language;
    //$result .= $template->getready('$.initposteditor();');
    $result .= $template->getready('initposteditor();');
    $ajax = tajaxposteditor ::i();
    return $ajax->dogethead($result);
  }
  
  private static function getsubcategories($parent, array $postitems) {
    $result = '';
    $categories = tcategories::i();
    $html = tadminhtml::getinstance('editor');
    $args = targs::i();
    foreach ($categories->items  as $id => $item) {
      if ($parent != $item['parent']) continue;
      $args->add($item);
      $args->checked = in_array($item['id'], $postitems);
      $args->subcount = '';
      $args->subitems = self::getsubcategories($id, $postitems);
      $result .= $html->category($args);
    }
    
    if ($result != '') $result = sprintf($html->categories(), $result);
    if ($parent == 0) $result = $html->categorieshead($args) . $result;
    return $result;
  }
  
  public static function getcategories(array $items) {
    $categories = tcategories::i();
    $categories->loadall();
    $result = self::getsubcategories(0, $items);
    return str_replace("'", '"', $result);
  }
  
  public static function getcombocategories(array $items, $idselected) {
    $result = '';
    $categories = tcategories::i();
    $categories->loadall();
    if (count($items) == 0) $items = array_keys($categories->items);
    foreach ($items as $id) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', tadminhtml::specchars($categories->getvalue($id, 'title')));
    }
    return $result;
  }
  
  protected function getpostcategories(tpost $post) {
    $postitems = $post->categories;
    $categories = tcategories::i();
    if (count($postitems) == 0) $postitems = array($categories->defaultid);
    return self::getcategories($postitems);
  }
  
  public function canrequest() {
    $this->isauthor = false;
    $this->basename = 'editor';
    $this->idpost = $this->idget();
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return 404;
    }
    $post = tpost::i($this->idpost);
    $groupname = litepublisher::$options->group;
    if ($groupname != 'admin') {
      $groups = tusergroups::i();
      if (!$groups->hasright($groupname, 'editor') &&  $groups->hasright($groupname, 'author')) {
        $this->isauthor = true;
        if (($post->id != 0) && (litepublisher::$options->user != $post->author)) return 403;
      }
    }
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::get($this->name, 'editor');
    }
  }
  
  public function getexternal() {
    $this->basename = 'editor';
    $this->idpost = 0;
    return $this->getcontent();
  }
  
  public function getpostargs(tpost $post, targs $args) {
    $args->id = $post->id;
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $args->title = htmlspecialchars_decode($post->title, ENT_QUOTES);
    $args->categories = $this->getpostcategories($post);
    $ajaxeditor = tajaxposteditor ::i();
    $args->editor = $ajaxeditor->getraweditor($post->rawcontent);
  }
  
  public function getcontent() {
    $html = $this->html;
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = targs::i();
    $this->getpostargs($post, $args);
    $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
    if ($this->isauthor &&($r = tauthor_rights::i()->getposteditor($post, $args)))  return $r;
    $result .= $html->form($args);
    unset(ttheme::$vars['post']);
    return $html->fixquote($result);
  }
  
  public static function processcategories() {
    return tadminhtml::check2array('category-');
  }
  
  protected function set_post(tpost $post) {
    extract($_POST, EXTR_SKIP);
    $post->title = $title;
    $post->categories = self::processcategories();
    if (($post->id == 0) && (litepublisher::$options->user >1)) $post->author = litepublisher::$options->user;
    if (isset($tags)) $post->tagnames = $tags;
    if (isset($icon)) $post->icon = (int) $icon;
    if (isset($idview)) $post->idview = $idview;
    if (isset($files))  {
      $files = trim($files);
      $post->files = $files == '' ? array() : explode(',', $files);
    }
    if (isset($date) && ($date != '')  && @sscanf($date, '%d.%d.%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
      $post->posted = mktime($h,$min,0, $m, $d, $y);
    }
    
    if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->comstatus = $comstatus;
      $post->pingenabled = isset($pingenabled);
      $post->idperm = (int) $idperm;
      if ($password != '') $post->password = $password;
    }
    
    if (isset($url)) {
      $post->url = $url;
      $post->title2 = $title2;
      $post->keywords = $keywords;
      $post->description = $description;
      $post->head = $head;
    }
    
    $post->content = $raw;
    if (isset($excerpt)) $post->excerpt = $excerpt;
    if (isset($rss)) $post->rss = $rss;
    if (isset($more)) $post->moretitle = $more;
    if (isset($filtered)) $post->filtered = $filtered;
    if (isset($upd)) {
      $update = sprintf($this->lang->updateformat, tlocal::date(time()), $upd);
      $post->content = $post->rawcontent . "\n\n" . $update;
    }
    
  }
  
  public function processform() {
    //dumpvar($_POST);
    $this->basename = 'editor';
    $html = $this->html;
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $id = (int)$_POST['id'];
    $post = tpost::i($id);
    if ($this->isauthor &&($r = tauthor_rights::i()->editpost($post)))  {
      $this->idpost = $post->id;
      return $r;
    }
    
    $this->set_post($post);
    $posts = tposts::i();
    if ($id == 0) {
      $this->idpost = $posts->add($post);
      $_POST['id'] = $this->idpost;
    } else {
      $posts->edit($post);
    }
    $_GET['id'] = $this->idpost;
    return sprintf($html->p->success,$post->bookmark);
  }
  
}//class

?>