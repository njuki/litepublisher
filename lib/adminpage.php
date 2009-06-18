<?php

class TAdminPage extends TEventClass {
 public $title;
 public $formresult;
 public $arg;
 public $id = 1;//for menu item template
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->CacheEnabled = false;
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
 
 public function Geturl() {
  global $Urlmap;
  return $Urlmap->url;
 }
 
 protected function ContentToForm($s) {
  $s = htmlspecialchars($s);
  $s = str_replace('"', '&quot;', $s);
  $s = str_replace("'", '&#39;', $s);
  return $s;
 }
 
 public function GetMenu() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  eval('$result = "'. $html->menu . '\n";');
  return $result;
 }
 
 public function Request($arg) {
  global $Options, $paths;
  $auth = &TAuthDigest::Instance();
  if (!$auth->Auth())  return $auth->Headers();
  //$Options->cookie = empty($_COOKIE['userid']) ? '' :$_COOKIE['userid'];
  $this->arg = $arg;
  TLocal::LoadLangFile('admin');
  $this->title = TLocal::$data[$this->basename]['title'];
  if (isset($_POST) && (count($_POST) > 0)) {
   if (get_magic_quotes_gpc()) {
    foreach ($_POST as $name => $value) {
     $_POST[$name] = stripslashes($_POST[$name]);
    }
   }
   $this->formresult= $this->ProcessForm();
  }
 }
 
 public function GetTemplateContent() {
  global $Options, $Template;
  $html = &THtmlResource::Instance();
  $html->section = 'index';
  eval('$result = "'. $html->content . '\n";');
  $result .= $this->GetMenu();
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
 
 public function confirmed() {
  return !empty($_GET['confirm']) && ($_GET['confirm'] == 1);
 }
 
 public function FixCheckall($s) {
  return str_replace('checkAll(document.getElementById("form"));', "checkAll(document.getElementById('form'));",    str_replace("'", '"', $s));
 }
 
}//class
?>