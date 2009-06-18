<?php

class TMenuItem extends TItem {
 
 public function GetBaseName() {
  return 'menus' . DIRECTORY_SEPARATOR . $this->id. DIRECTORY_SEPARATOR. 'index';
 }
 
 public static function &Instance($id = 0) {
  if ($id == 0) {
   $classname= __class__;
  } else {
   $menu = &TMenu::Instance();
   $classname = $menu->GetValue($id, 'class');
  }
  return parent::Instance($classname, $id);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->Data= array(
  'author' => 0, //not supported
  'order' => 0,
  'parent' => 0,
  'date' => 0,
  'title' => '',
  'url' => '',
  'content' => '',
  'rawcontent' => '',
  'keywords' => '',
  'description' => '',
  'status' => 'published',
  'password' => '',
  'theme' => '',
  );
 }
 
 public function GetTemplateContent() {
  global $Template;
  $GLOBALS['post'] = &$this;
  $tml = 'menuitem.tml';
  if ($this->theme <> '') {
   if (@file_exists($Template->path . $this->theme)) $tml = $this->theme;
  }
  return $Template->ParseFile($tml);
 }
 
 public function &Getsubmenu() {
  global $Options;
  $result = array();
  $menu = &TMenu::Instance();
  $Childs = $menu->items[$this->id]['childs'];
  if (count($Childs) > 0) {
   $Items = &$menu->items;
   foreach ($Childs as $id) {
    $title =  $Items[$id]['title'];
    $result[] = '<a href="'. $Options->url . $Items[$id]['url'] . "\" title=\"$title\">$title</a>";
   }
  }
  return $result;
 }
 
}

?>