<?php

class TAdminPage extends TEventClass {
  public $title;
  public $menu;
  public $formresult;
  public $arg;
public $group;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = strtolower(get_class($this));
    $this->CacheEnabled = false;
    $this->menu = '';
    $this->formresult = '';
  }
  
  public function getbasename() {
    return 'admin' . DIRECTORY_SEPARATOR . $this->basename;
  }
  
  public function install() {
    if (get_class($this) == __class__) return;
    $urlmap = turlmap::instance();
    $urlmap->add('/admin/$this->basename/', get_class($this), null, 'tree');
  }
  
  public function uninstall() {
    turlmap::unsub($this);
  }
  
  public function __get($name) {
    if ($name == 'content') return $this->formresult . $this->getcontent();
    return parent::__get($name);
  }
  
  public function getsubtheme() {
    $template = ttemplate::instance();
    if (file_exists($template->path . 'admin.tml')) return 'admin.tml';
    return 'index.tml';
  }
  
  public function geturl() {
    global $urlmap;
    return $urlmap->url;
  }
  
  public function ContentToForm($s) {
    $s = htmlspecialchars($s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace("'", '&#39;', $s);
    return $s;
  }
  
  public function getmenu() {
    global $options;
    $html = THtmlResource::instance();
    $html->section = 'index';
    $lang = tlocal::instance();
    eval('$this->menu .=  "'. $html->content . '\n";');
    
    $html->section = $this->basename;
    eval('$this->menu .= "'. $html->menu . '\n";');
    return $this->menu;
  }
  
  public function auth() {
    global $options, $urlmap;
    $auth = tauthdigest::instance();
    if ($auth->cookieenabled) {
if ($s = $auth->checkattack()) return $s;
if (!$auth->authcookie()) return $urlmap->redir301('/admin/login/');
      } 
elseif (!$auth->Auth())  return $auth->headers();      

if ($options->group != 'admin') {
$groups = tusergroups::instance();
if ($groups->hasright($options->group, $this->group)) return 404;
}
  }
  
  public function request($arg) {
    if ($s = $this->auth()) return $s;
    $this->arg = $arg;
    tlocal::loadlang('admin');
    $this->title = tlocal::$data[$this->basename]['title'];

    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      $this->formresult.= $this->processform();
    }
  }
  
  public function GetTemplateContent() {
    global $options;
    $template = ttemplate::instance();
    $result = $this->Ggetmenu();
    $result = str_replace("'", '"', $result);
    $GLOBALS['post'] = &$this;
    $result .= $Template->ParseFile('menuitem.tml');
    return $result;
  }
  
  public function processform() {
    return '';
  }
  
  public function idget() {
    return !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
  }
  
public function gethtml() {
$result = THtmlResource::instance();
$result->section = $this->basename;
$lang = tlocal::instance($this->basename);
return $result;
}

  public function Getconfirmed() {
    return !empty($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function FixCheckall($s) {
    return str_replace('checkAll(document.getElementById("form"));', "checkAll(document.getElementById('form'));",    str_replace("'", '"', $s));
  }
  
  public function notfound() {
return $this->html->notfound();
  }
  
  public function Getadminurl($section, $arg) {
    global $options;
    return "$options->url/admin/$section/$options->q$arg";
  }
  
}//class
?>