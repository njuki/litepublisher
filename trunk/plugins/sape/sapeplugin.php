<?php

class TSapePlugin extends TPlugin {
public $sape;
public $widgets;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

 protected function CreateData() {
  parent::CreateData();
$this->Data['user'] = '';
$this->Data['count'] = 2;
$this->Data['force'] = false;
$this->Data['optimize'] = false;
$this->Data['optcode'] = '';
$this->AddDataMap('widgets', array());
}

 public function AfterLoad() {
parent::AfterLoad();
     if (!defined('_SAPE_USER')){
       define('_SAPE_USER', $this->user);
 include_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'sape.php');
$o['charset'] = 'UTF-8';
$o['multi_site'] = true;
if ($this->force) $o['force_show_code'] = $this->force;
$this->sape = new SAPE_client($o);
}
}

public static function PrintLinks($count = null) {
$self = &GetInstance(__class__);
echo $self->GetLinks($count);
}

public function GetLinks($count = null) {
global $Urlmap;
if ($Urlmap->is404 || $Urlmap->IsAdminPanel) return '';
if (isset($this->sape)) {
 $Links = $this->sape->return_links($count);
if (!empty($Links)) {
return "<li>$Links</li>\n";
}
}
return '';
}

 public function GetWidgetContent($id) {
  global  $Template;
    $result = $Template->GetBeforeWidget('links');
$result .= $this->GetLinks($this->count);
  $result .= $Template->GetAfterWidget();
  return $result;
}

public function AfterWidget($id) {
  global  $Template;
if (!in_array($Template->widgets[$id]['class'], $this->widgets))  return '';
if ($this->optimize) {
return "<!--$this->optcode-->\n";
} else {
return '<?php '. get_class($this) . "::PrintLinks($this->count); ?>\n";
}
}

public function Setoptimize($value) {
global $Urlmap;
if ($this->optimize != $value) {
$this->Data['optimize'] = $value;
$this->Data['optcode'] = md5(secret. uniqid( microtime()) . 'sapeplugin optimize');
$this->Save();

$Template = &TTemplate::Instance();
if  ($value) {
$Template->OnWidgetContent = $this->OnWidgetContent;
} else {
$Template->UnsubscribeEvent('OnWidgetContent', get_class($this));
}
$Urlmap->ClearCache();
}
}

public function OnWidgetContent($s) {
$code = "<!--$this->optcode-->";
if (strpos($s, $code) !== false) {
return str_replace($code, $this->GetLinks($this->count), $s);
}
return '';
}
 
}//class
?>