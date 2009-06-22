<?php

class TAdminWidgets extends TAdminPage {
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'widgets';
 }
 
 private function GetSortnameCombobox ($comboname, $sortname) {
  $result = "<select name='$comboname' id='$comboname'>\n";
  foreach (TLocal::$data['sortnametags'] as $name => $value) {
   $selected = $sortname  == $name? 'selected' : '';
   $result .= "<option value='$name' $selected>$value</option>\n";
  }
  $result .= "</select>";
  return $result;
 }
 
 public function Getcontent() {
  global $Options, $Template;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  
  $checked = "checked='checked'";
  switch ($this->arg) {
   case null:
   $result = $html->checkallscript;
   $result .= $html->formhead;
   $item = $html->item;
   $sitebarscount = 3;
   for ($i = 0; $i < $sitebarscount; $i++) {
    
    $combolist = '';
    for ($k = 1; $k <= $sitebarscount; $k++) {
     $selected = $k == $i+ 1 ? 'selected' : '';
     $combolist .= "<option $selected>$k</option>\n";
    }
    
    $count = count($Template->sitebars[$i]);
    for  ($j = 0; $j < $count; $j++) {
     $id = $Template->sitebars[$i][$j];
     $widget = $Template->widgets[$id];
     if (isset(TLocal::$data['stdwidgetnames'][$widget['class']])) {
      $name = TLocal::$data['stdwidgetnames'][$widget['class']];
     } else {
      $name = $this->GetWidgetName($widget['class'], $id);
     }
     $orderlist = '';
     for ($k = 1; $k <= $count; $k++) {
      $selected = $k == $j + 1 ? 'selected' : '';
      $orderlist.= "<option $selected>$k</option>\n";
     }
     $sitebarcombo = "<select name='widgetsitebar-$id' id='widgetsitebar-$id'>\n$combolist</select>\n";
     $ordercombo = "<select name='widgetorder-$id' id='widgetorder-$id'>\n$orderlist</select>\n";
     eval('$result .= "'. $item . '\n";');
    }
   }
   eval('$result .= "'. $html->formfooter . '\n";');;
   return  $this->FixCheckall($result);
   
   case 'std':
   $Template = &TTemplate::Instance();
   $result = $html->stdheader;
   $item = $html->stditem;
   foreach (TLocal::$data['stdwidgetnames'] as $class => $name) {
    if ($class == 'TCustomWidget') continue;
    $selected = !$Template->ClassHasWidget($class) ? $checked : '';
    eval('$result .= "' . $item . '\n";');
   }
   eval('$result .= "'. $html->stdfooter . '\n";');;
   break;
   
   case 'stdoptions':
   $archives = &TArchives::Instance();
   $showcountarch = $archives->showcount ? $checked : '';
   
   $categories = &TCategories::Instance();
   $showcountcats = $categories->showcount ? $checked : '';
   $catscombo= $this->GetSortnameCombobox('sortnamecats', $categories->sortname);
   
   $tags = &TTags::Instance();
   $showcounttags = $tags->showcount ? $checked : '';
   $tagscombo= $this->GetSortnameCombobox('sortnametags', $tags->sortname);
   
   $posts = &TPosts::Instance();
   $comments = &TCommentManager::Instance();
   //$meta = &TMetaWidget::Instance();
   $links = &TLinksWidget::Instance();
   $lwredir = $links->redir ? $checked : '';
   
   $foaf = &TFoaf::Instance();
   $foafredir = $foaf->redir ? $checked : '';
   eval('$result = "'. $html->stdoptionsform . '\n";');
   break;
   
   case 'links':
   $links = TLinksWidget::Instance();
   $result = $html->linkshead;
   $id = $this->idget();
   if ($id > 0) {
    $url = $this->ContentToForm($links->items[$id]['url']);
    $title = $this->ContentToForm($links->items[$id]['title']);
    $text = $this->ContentToForm($links->items[$id]['text']);
    $result .= sprintf($html->editlink, $url);
   } else {
    $result .= $html->newlink;
    $url = '';
    $title = '';
    $text = '';
   }
   eval('$result .= "'. $html->linkform . '\n";');
   
   $result .= $html->checkallscript;
   $result .= $html->linkstable;
   $linkitem = $html->linkitem;
   foreach ($links->items as $id => $item) {
    eval('$result .= "' .$linkitem . '\n";');
   }
   eval('$result .= "'. $html->linkstablefooter . '\n";');;
   return $this->FixCheckall($result);
   
   case 'custom':
   $widget = &TCustomWidget::Instance();
   $result = $html->customhead;
   $result .= "\n<ul>\n";
   foreach ($widget->items as $id => $item) {
    $result .= "<li><a href='$Options->url/admin/widgets/custom/?id=$id'>$item[title]</a></li>\n";
   }
   $result .= "</ul>\n";
   $id = $this->idget();
   if ($id > 0) {
    $title = $this->ContentToForm($widget->items[$id]['title']);
    $content = $this->ContentToForm($widget->items[$id]['content']);
    $result .= sprintf($html->editcustom, $title);
   } else {
    $result .= $html->newcustom;
    $title = '';
    $content = '';
   }
   eval('$result .= "'. $html->customform . '\n";');
   break;
  }
  
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Urlmap;
  $Template = &TTemplate::Instance();
  switch ($this->arg) {
   case null:
   if (!empty($_POST['deletewidgets'])) {
    return $this->DeleteWidgets();
   }
   $Template->Lock();
   $check = 'widgetcheck-';
   $sitebar = 'widgetsitebar-';
   $order =  'widgetorder-';
   foreach ($_POST as $key => $value) {
    if ($check == substr($key, 0, strlen($check))){
     $checkid = (int) substr($key, strlen($check));
    } elseif ($sitebar == substr($key, 0, strlen($sitebar))) {
     $id = (int) substr($key, strlen($sitebar));
     if ($id == $checkid) $Template->MoveWidget($id, $value - 1);
    } elseif ($order == substr($key, 0, strlen($order))) {
     $id = (int) substr($key, strlen($order));
     if ($id == $checkid) $Template->MoveWidgetOrder($id, $value - 1);
    }
   }
   $Template->Unlock();
   $rname = 'success';
   break;
   
   case 'std':
   $Template->Lock();
   foreach (TLocal::$data['stdwidgetnames'] as $class => $name) {
    if (isset($_POST[$class]) && !$Template->ClassHasWidget($class)) {
     $Template->AddWidget($class, 'echo');
    }
   }
   $Template->Unlock();
   $rname = 'stdsuccess';
   break;
   
   case 'stdoptions':
   extract($_POST);
   
   $archives = &TArchives::Instance();
   if (isset($showcountarch) != $archives->showcount) {
    $archives->showcount = isset($showcountarch);
    $archives->Save();
   }
   
   $categories = &TCategories::Instance();
   $categories->SetParams($categories->lite, $sortnamecats, isset($showcountcats), 0);
   
   $tags = &TTags::Instance();
   $tags->SetParams($tags->lite, $sortnametags, isset($showcounttags), $maxcount);
   
   $posts = &TPosts::Instance();
   $posts->recentcount = (int) $postscount;
   
   $comments = &TCommentManager::Instance();
   $comments->recentcount = $commentscount;
   
   $links = &TLinksWidget::Instance();
   $links->redir = isset($lwredir);
   $links->Save();
   
   $foaf = &TFoaf::Instance();
   $foaf->SetParams($friendscount, $foafredir);
   
   $rname = 'stdoptsucces';
   break;
   
   case 'links':
   $links = TLinksWidget::Instance();
   $links->Lock();
   if (!empty($_POST['delete'])) {
    foreach ($_POST as $id => $value) {
     if ($links->ItemExists($id)) {
      $links->Delete($id);
     }
    }
    $rname = 'linksdeleted';
   } else {
    extract($_POST);
    $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($links->ItemExists($id)) {
     $links->Edit($id, $url, $title, $text);
    } else {
     $links->Add($url, $title, $text);
    }
    $rname = 'linkedited';
   }
   $links->Unlock();
   break;
   
   case 'custom':
   extract($_POST);
   $widget = &TCustomWidget::Instance();
   $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
   if ($id > 0) {
    $widget->Edit($id, $title, $content);
   } elseif (!empty($title) || !empty($content)) {
    $widget->add($title, $content);
   }
   $rname = 'customsuccess';
   break;
  }
  
  $Urlmap->ClearCache();
  
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
 return $html->{$rname};
 }
 
 protected function DeleteWidgets() {
  global $Urlmap;
  $Template = &TTemplate::Instance();
  $Template->Lock();
  $check = 'widgetcheck-';
  foreach ($_POST as $key => $value) {
   if ($check == substr($key, 0, strlen($check))){
    $id = (int) substr($key, strlen($check));
    $Template->DeleteIdWidget($id);
   }
  }
  $Template->Unlock();
  $Urlmap->ClearCache();
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  
  return $html->successdeleted;
 }
 
 public function GetWidgetName($class, $id) {
  if ($class == 'TCustomWidget') {
   $widget = &TCustomWidget::Instance();
   return $widget->items[$id]['title'];
  }
  
  //if widget is plugin then get from about.ini
  $plugins = &TPlugins::Instance();
  foreach ($plugins->items as $name => $item) {
   if ($class == $item['class']) {
    $about = $plugins->GetAbout($name);
    return $about['name'];
   }
  }
  return '';
 }
 
}//class
?>