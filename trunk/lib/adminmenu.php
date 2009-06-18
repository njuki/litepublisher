<?php

class TAdminMenu extends TAdminPage {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'menu';
 }
 
 public function Getcontent() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  
  switch ($this->arg) {
   case null:
   if (isset($_GET['action'])) return $this->ProcessAction();
   $menu = &TMenu::Instance();
   $result = $html->listhead;
   foreach ($menu->items as $id => $item) {
    $post = &TMenuItem::Instance($id);
    $status = TLocal::$data['poststatus'][$post->status];
    if ($item['parent'] == 0) {
     $parent = '---';
    } else {
     $parent = "<a href='" . $Options->url . $menu->items[$item['parent']]['url'] . "'>". $menu->items[$item['parent']]['title'] . '</a>';
    }
    eval('$result .="' . $html->itemlist . '\n" ;');
   }
   $result .= $html->listfooter;
   $result = str_replace("'", '"', $result);
   break;
   
   case 'edit':
   $id = !empty($_GET['postid']) ? (int) $_GET['postid'] : (!empty($_POST['postid']) ? (int) $_POST['postid'] : 0);
   $post = &TMenuItem::Instance($id);
   $content = $this->ContentToForm($post->content);
   $menu = &TMenu::Instance();
   $selected = $post->parent  == 0 ? 'selected' : '';
   $parentcombo = "<option value='0' $selected>---</option>\n";
   foreach ($menu->items as $id => $item) {
    if ($id != $post->id) {
     $selected = $post->parent  == $id ? 'selected' : '';
     $parentcombo .= "<option value='$id' $selected>$item[title]</option>\n";
    }
    
   }
   eval('$result = "' . $html->editform . '\n";');
   break;
  }
  
  return $result;
 }
 
 public function ProcessForm() {
  global $Options;
  extract($_POST);
  switch ($this->arg) {
   case null:
   break;
   
   case 'edit':
   if (empty($title) || empty($content)) return '';
   $id = !empty($_GET['postid']) ? (int) $_GET['postid'] : (!empty($_POST['postid']) ? (int) $_POST['postid'] : 0);
   $post = &TMenuItem::Instance($id);
   $post->title = $title;
   $post->order = (int) $order;
   $post->parent = (int) $parent;
   $post->content = $content;
   
   $menu = &TMenu::Instance();
   if ($id == 0) {
    $menu->Add($post);
   } else  {
    $menu->Edit($post);
   }
   break;
  }
  return '';
 }
 
 public function ProcessAction() {
  global $Options;
  $id = (int) $_GET['postid'];
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  
  $menu = &TMenu::Instance();
  if (!$menu->ItemExists($id)) {
   return $html->notfound;
  }
  
  $post = &TMenuItem::Instance($id);
  
  $result ='';
  if  (isset($_GET['confirm']) && ($_GET['confirm'] == 1)) {
   switch ($_GET['action']) {
    case 'delete' :
    $menu->Delete($id);
    break;
    
    case 'setdraft':
    $post->status = 'draft';
    $menu->Edit($post);
    break;
    
    case 'publish':
    $post->status = 'published';
    $menu->Edit($post);
    break;
   }
   $result .=  sprintf($html->confirmed, TLocal::$data[$this->basename][$_GET['action']], "<a href='$Options->url$post->url'>$post->title</a>");
  } else {
   $lang = &TLocal::$data[$this->basename];
   $confirm = sprintf($lang['confirm'], $lang[$_GET['action']], "<a href='$Options->url$post->url'>$post->title</a>");
   $yes = TLocal::$data['default']['yesword'];
   eval('$result .= "'. $html->confirmform . '\n";');
  }
  return $result;
 }
 
}//class
?>