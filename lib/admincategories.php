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
    $html = &THtmlResource::Instance();
    $html->section = 'posts';
    $lang = &TLocal::Instance();
    
    eval('$result = "'. $html->menu . '\n";');
    return  $result;
  }
  
  public function Getcontent() {
    global $Options, $classes;
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    $lang->section = $this->basename;
    $class = !empty($_GET['class']) ? $_GET['class'] : 'TCategories';
    $istag = $class == $classes->classes['tags'];
    if (isset($_GET['full'])) {
      $form = $class == 'TTags' ? 'tagfullform' : 'catfullform';
    } else {
      $form = 'tagform';
    }
    $tags = GetInstance($class);
    $id = $this->idget();
    if ($id ==  0) {
      $name = '';
      if ($class == 'TTags') {
        eval('$result = "'. $html->addtag . '\n";');
      } else {
        eval('$result = "'. $html->addcategory. '\n";');
      }
    eval('$result .= "'. $html->{$form} . '\n";');
    } elseif (!$tags->ItemExists($id)) {
      eval('$result = "'. $html->notfound. '\n";');
    } else {
      $name = $tags->items[$id]['name'];
      if (!empty($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $tags->Delete($id);
          eval('$result = "'. $html->successdeleted. '\n";');
        } else {
          eval('$result = "'. $html->confirmdelete . '\n";');
        }
      } else {
        
      $result = $html->{ $istag ? 'edittag' : 'editcategory'} ($name);
        
        $url = $tags->items[$id]['url'];
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
    eval('$result .= "'. $html->listhead. '\n";');
    $itemlist = $html->itemlist;
    foreach ($tags->items as $id => $item) {
      eval('$result .= "'. $itemlist . '\n";');
    }
    eval('$result .= "'. $html->listfooter. '\n";');;
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function ProcessForm() {
    global $Options;
    if (empty($_POST['name'])) return '';
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    $name = $_POST['name'];
    
    $class = !empty($_GET['class']) ? $_GET['class'] : 'TCategories';
    $tags = GetInstance($class);
    $id = $this->idget();
    if ($id == 0) {
      $id = $tags->Add($name);
    } else {
      $tags->Edit($id, $name, $tags->items[$id]['url']);
      if (isset($_GET['full'])) {
        $tags->Edit($id, $name, $_POST['url']);
        if ($class == 'TCategories') $tags->SetItemContent($id, $_POST['content']);
      } else {
        $tags->Edit($id, $name, $tags->items[$id]['url']);
      }
    }
    
    eval('$s = "'. $html->success. '\n";');
    return sprintf($s, $name);
  }
  
}//class
?>