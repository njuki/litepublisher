<?php

class TAdminCategories extends TAdminPage {
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'categories';
 }
 
 public function GetMenu() {
  global $Options;
  eval('$result = "'. TLocal::$data['posts']['menu'] . '\n";');
  return  $result;
 }
 
 public function Getcontent() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  
  $class = !empty($_GET['class']) ? $_GET['class'] : 'TCategories';
  $form = $class == 'TTags' ? 'tagform' : 'catform';
  $tags = GetInstance($class);
  $id = $this->idget();
  if ($id ==  0) {
   $name = '';
   $content = '';
   if ($class == 'TTags') {
    $result = $html->addtag;
   } else {
    $result = $html->addcategory;
   }
  eval('$result .= "'. $html->{$form} . '\n";');
  } elseif (!$tags->ItemExists($id)) {
   $result = $html->notfound;
  } else {
   $name = $tags->items[$id]['name'];
   if (!empty($_GET['action']) &&($_GET['action'] == 'delete'))  {
    if  (!empty($_GET['confirm']) && ($_GET['confirm'] == 1)) {
     $tags->Delete($id);
     $result = $html->successdeleted;
    } else {
     eval('$result = "'. $html->confirmdelete . '\n";');
    }
   } else {
   $result = sprintf($html->{ $class == 'TTags' ? 'edittag' : 'editcategory'}, $name);
    if ($class == 'TCategories') {
     if ($desc = $tags->GetItemContent($id)) {
      $content = $this->ContentToForm($desc['content']);
     } else {
      $content = '';
     }
    }
   eval('$result .= "'. $html->{$form} . '\n";');
   }
  }
  
  //table
  $result .= $html->listhead;
  $itemlist = $html->itemlist;
  foreach ($tags->items as $id => $item) {
   eval('$result .= "'. $itemlist . '\n";');
  }
  $result .= $html->listfooter;
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Options;
  if (!empty($_POST['name'])) {
   $name = $_POST['name'];
   $content = isset($_POST['content']) ? $_POST['content'] : '';
   $html = &THtmlResource::Instance();
   $html->section = $this->basename;
   
   $class = !empty($_GET['class']) ? $_GET['class'] : 'TCategories';
   $tags = GetInstance($class);
   $id = $this->idget();
   if ($id == 0) {
    $id = $tags->Add($name);
   } else {
    $tags->Edit($id, $name, $tags->items[$id]['url']);
   }
   if ($class == 'TCategories') $tags->SetItemContent($id, $content);
   return  sprintf($html->success, $name);
  }
 }
 
}//class
?>