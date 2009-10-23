<?php

class TAdminPage extends TEventClass {
  public $title;
  public $menu;
  public $formresult;
  public $arg;
  public $id = 1;//for menu item template
  
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
    global $options, $urlmap, $paths;
    $auth = TAuthDigest::instance();
    if (!($auth->cookieenabled && $urlmap->mobile)) {
      if (!$auth->Auth())  return $auth->headers();
    } else {
      if ($auth->xxxcheck) {
        if (empty($_SERVER['HTTP_REFERER'])) {
          $p = '';
        } else {
          $p = parse_url($_SERVER['HTTP_REFERER']);
          $p = $p['host'];
        }
        if ( $p != $_SERVER['HTTP_HOST'] ) {
          if ($_POST) die('<b><font color="red">Achtung! XSS attack!</font></b>');
      if ($_GET)  die("<b><font color=\"maroon\">Achtung! XSS attack?</font></b><br>Confirm transition: <a href=\"{$_SERVER['REQUEST_URI']}\">{$_SERVER['REQUEST_URI']}</a>");
        }
      }
      if (empty($_COOKIE['admin']) || ($auth->cookie != $_COOKIE['admin']) || ($auth->cookieexpired < time())) return "<?php @header('Location: $options->url/admin/login/'); ?>";
      
      $html = THtmlResource::instance();
      $html->section = 'login';
      $lang = tlocal::instance();
      eval('$this->menu .= "'. $html->logout . '\n";');
    }
  }
  
  public function request($arg) {
    if ($s = $this->auth()) return $s;
    $this->arg = $arg;
    tlocal::LoadLangFile('admin');
    $this->title = tlcal::$data[$this->basename]['title'];
    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      $this->formresult.= $this->ProcessForm();
    }
  }
  
  public function GetTemplateContent() {
    global $options;
    $Template = ttemplate::instance();
    $result = $this->Ggetmenu();
    $result = str_replace("'", '"', $result);
    $GLOBALS['post'] = &$this;
    $result .= $Template->ParseFile('menuitem.tml');
    return $result;
  }
  
  public function ProcessForm() {
    return '';
  }
  
  public function idget() {
    return !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
  }
  
  public function Getconfirmed() {
    return !empty($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function FixCheckall($s) {
    return str_replace('checkAll(document.getElementById("form"));', "checkAll(document.getElementById('form'));",    str_replace("'", '"', $s));
  }
  
  public function notfound() {
    $html = THtmlResource::instance();
    $html->section = $this->basename;
    $lang = tlocal::instance();
    eval('$result = "'. $html->notfound  . '\n";');
    return $result;
  }
  
  public function Getadminurl($section, $arg) {
    global $options;
    return "$options->url/admin/$section/$options->q$arg";
  }
  
}//class
?>