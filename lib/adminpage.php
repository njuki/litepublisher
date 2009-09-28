<?php

class TAdminPage extends TEventClass {
  public $title;
  public $menu;
  public $formresult;
  public $arg;
  public $id = 1;//for menu item template
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->CacheEnabled = false;
    $this->menu = '';
    $this->formresult = '';
  }
  
  public function GetBaseName() {
    return 'admin' . DIRECTORY_SEPARATOR . $this->basename;
  }
  
  public function Install() {
    if (get_class($this) == __class__) return;
    $urlmap = &TUrlmap::Instance();
    $urlmap->AddFinalNode('admin', $this->basename, get_class($this), null);
  }
  
  public function Uninstall() {
    TUrlmap::unsub($this);
  }
  
  public function __get($name) {
    if ($name == 'content') {
      return $this->formresult . $this->Getcontent();
    }
    return parent::__get($name);
  }
  
  public function Gettemplate() {
    $template = TTemplate::Instance();
    if (file_exists($template->path . 'admin.tml')) return 'admin.tml';
    return 'index.tml';
  }
  
  public function Geturl() {
    global $Urlmap;
    return $Urlmap->url;
  }
  
  public function ContentToForm($s) {
    $s = htmlspecialchars($s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace("'", '&#39;', $s);
    return $s;
  }
  
  public function GetMenu() {
    global $Options;
    $html = &THtmlResource::Instance();
    $html->section = 'index';
    $lang = &TLocal::Instance();
    eval('$this->menu .=  "'. $html->content . '\n";');
    
    $html->section = $this->basename;
    eval('$this->menu .= "'. $html->menu . '\n";');
    return $this->menu;
  }
  
  public function Auth() {
    global $Options, $Urlmap, $paths;
    $auth = &TAuthDigest::Instance();
    if (!($auth->cookieenabled && $Urlmap->Ispda)) {
      if (!$auth->Auth())  return $auth->Headers();
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
      if (empty($_COOKIE['admin']) || ($auth->cookie != $_COOKIE['admin']) || ($auth->cookieexpired < time())) return "<?php @header('Location: $Options->url/admin/login/'); ?>";
      
      $html = &THtmlResource::Instance();
      $html->section = 'login';
      $lang = &TLocal::Instance();
      eval('$this->menu .= "'. $html->logout . '\n";');
    }
  }
  
  public function Request($arg) {
    if ($s = $this->Auth()) return $s;
    $this->arg = $arg;
    TLocal::LoadLangFile('admin');
    $this->title = TLocal::$data[$this->basename]['title'];
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
    global $Options;
    $Template = TTemplate::Instance();
    $result = $this->GetMenu();
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
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    eval('$result = "'. $html->notfound  . '\n";');
    return $result;
  }
  
  public function Getadminurl($section, $arg) {
    global $Options;
    return "$Options->url/admin/$section/$Options->q$arg";
  }
  
}//class
?>