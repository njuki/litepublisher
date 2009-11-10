<?php

class tadminwidgets extends tadminmenuitem {

  public static function instance() {
    return getinstance(__class__);
  }
  
  private function GetSortnameCombobox ($comboname, $sortname) {
    $result = "<select name='$comboname' id='$comboname'>\n";
    foreach (tlocal::$data['sortnametags'] as $name => $value) {
      $selected = $sortname  == $name? 'selected' : '';
      $result .= "<option value='$name' $selected>$value</option>\n";
    }
    $result .= "</select>";
    return $result;
  }

private function getcombo($name, $index, $count) {
$index++;
          $result = "<select name='$name' id='$name'>\n"
        for ($i = 1; $i <= $count; $i++) {
          $selected = $i == $index  ? 'selected' : '';
          $result .= "<option $selected>$i</option>\n";
        }
$result .= </select>\n";        
return $result;
}

private function getwidgettitle($id) {
$widgets = twidgets::instance();
          $widget = $widgets->items[$id];
if !empty($widget['title'])) return $widget['title'];
          if (isset(tlocal::$data['stdwidgetnames'][$widget['class']])) {
return TLocal::$data['stdwidgetnames'][$widget['class']];
          }

$std = tstdwidgets::instance();
if ($name = $std->getname($id)) {
return $std->gettitle($name);
}

$class = $widget['class'];
    if ($class == 'tcustomwidget') {
      $custom = tcustomwidget ::instance();
      return $custom->items[$id]['title'];
    }
    
    //if widget is plugin then get from about.ini
    $plugins = tplugins::instance();
    foreach ($plugins->items as $name => $item) {
      if ($class == $item['class']) {
        $about = $plugins->GetAbout($name);
        return $about['name'];
      }
    }
    return '';
}
  
  public function getcontent() {
    global $classes, $options;
$result = '';
$widgets = twidgets::instance();
$sitebars = tsitebars::instance();
$html = $this->html;
$args = targs::instance();    

    switch ($this->name) {
      case 'widgets':
      $result = $html->checkallscript;
$result .= $html->formhead();
// принимается что макс число сайтбаров = 3
      for ($i = 0; $i < 3; $i++) {
        for  ($j = 0; $j < $sitebars->getcount($i); $j++) {
          $args->id = $sitebars->items[$i][$j];
$args->title = $this->getwidgettitle($args->id);
          $args->sitebarcombo = $this->getcombo('"sitebar-$id", $i, 3);
          $args->ordercombo = $this->getcombo("order-$id", $j, $sitebars->getcount($i));
$result .= $html->item($args);
        }
      }
      $result .= $html->formfooter();
      return  $this->FixCheckall($result);
      
      case 'std':
$std = tstdwidgets::instance();
$result = $html->stdheader();
foreach ($std->names as $name) {
$args->checked = isset($std->items[$name]);
$args->ajax = isset($std->items[$name]) ? $std->items[$name]['ajax'] : true;
$args->name = $name;
$args->title = $std->gettitle($name);
$result .= $html->stditem($args);
      }
      $result .= $html->stdfooter();
      break;
      
      case 'stdoptions':
      $archives = tarchives::instance();
      $args->showcountarch = $archives->showcount;
            $catsoptions = $classes->categories->options;
      $args->showcountcats = $catsoptions->showcount;
      $args->catscombo= $this->GetSortnameCombobox('sortnamecats', $catsoptions->sortname);
      
      $tagsoptions = $classes->tags->options;
      $args->showcounttags = $tagsoptions->showcount;
      $args->tagscombo= $this->GetSortnameCombobox('sortnametags', $tagsoptions->sortname);
      
      $posts = tposts::instance();
      $comments = &TCommentManager::instance();
      //$meta = &TMetaWidget::instance();
      $links = &TLinksWidget::instance();
      $args->lwredir = $links->redir;
      
      $foaf = tfoaf::instance();
      $args->foafredir = $foaf->redir ? $checked : '';
      $result = $html->stdoptionsform($args);
      break;
      
      case 'links':
      $links = TLinksWidget::instance();
      eval('$result = "'. $html->linkshead . '\n";');
      $id = $this->idget();
      if ($id > 0) {
        $url = $this->ContentToForm($links->items[$id]['url']);
        $title = $this->ContentToForm($links->items[$id]['title']);
        $text = $this->ContentToForm($links->items[$id]['text']);
        eval('$s= "'. $html->editlink. '\n";');
        $result .= sprintf($s, $url);
      } else {
        eval('$result .= "'. $html->newlink . '\n";');
        $url = '';
        $title = '';
        $text = '';
      }
      eval('$result .= "'. $html->linkform . '\n";');
      
      $result .= $html->checkallscript;
      eval('$result .= "'. $html->linkstable . '\n";');
      $linkitem = $html->linkitem;
      foreach ($links->items as $id => $item) {
        eval('$result .= "' .$linkitem . '\n";');
      }
      eval('$result .= "'. $html->linkstablefooter . '\n";');;
      return $this->FixCheckall($result);
      
      case 'custom':
      $widget = &TCustomWidget::instance();
      eval('$result = "'. $html->customhead . '\n";');
      $result .= "<ul>\n";
      foreach ($widget->items as $id => $item) {
      $result .= "<li><a href='$options->url/admin/widgets/custom/?id=$id' title='widget $id'>{$item['title']}</a></li>\n";
      }
      $result .= "</ul>\n";
      $id = $this->idget();
      if ($id > 0) {
        $title = $this->ContentToForm($widget->items[$id]['title']);
        $content = $this->ContentToForm($widget->items[$id]['content']);
        $templ = $widget->items[$id]['templ']? $checked : '';
        eval('$s = "'. $html->editcustom. '\n";');
        $result .= sprintf($s, $title);
      } else {
        eval('$result .= "'. $html->newcustom . '\n";');
        $title = '';
        $content = '';
        $templ = $checked;
      }
      eval('$result .= "'. $html->customform . '\n";');
      break;
    }
    
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function ProcessForm() {
    global $Urlmap;
$widgets = twidgets::instance();
    switch ($this->arg) {
      case 'widgets':
      if (!empty($_POST['deletewidgets'])) {
        return $this->DeleteWidgets();
      }
      $widgets->lock();
$sitebars = tsitebars::instance();
      $check = 'widgetcheck-';
      $sitebar = 'sitebar-';
      $order =  'order-';
      $checkid =       0;
      foreach ($_POST as $key => $value) {
        if (strbegin($key, $check)){
          $checkid = (int) substr($key, strlen($check));
          continue;
        } elseif (strbegin($key, $sitebar)) {
          $id = (int) substr($key, strlen($sitebar));
          if ($id == $checkid) $widgets->changesitebar($id, $value - 1);
        } elseif (strbegin($key, $order)) {
          $id = (int) substr($key, strlen($order));
          if ($id == $checkid) $widgets->changeorder($id, $value - 1);
        }
      }
      $widgets->unlock();
$sitebars->save();
return $this->html->h2->success;

      case 'std':
//подготовить массив с именами виджетов
      array_pop($_POST);
$names = array();
foreach ($_POST as $name=> $value) {
if (strbegin($name, 'ajax-')) {
$name = substr($name, strlen('ajax-');
if (isset($names[$name])) $names[$name] = true;
} else {
$names[$name] = false;
}
}
$widgets->lock();
      $std = tstdwidgets::instance();
$std->lock();
foreach ($std->names as $name) {
if (isset($std->items[$name])) {
if (isset($names[$name])) {
$std->items[$name]['ajax'] = $names[$name];
} else {
$std->delete($name);
}
} elseif (isset($names[$name])) {
$std->add($name, $names[$name]);
}
}
$std->updateajax();
$std->unlock();
$widgets->unlock();
return $this->html->h2->stdsuccess;
    
      case 'stdoptions':
      extract($_POST);
      
      $archives = &TArchives::instance();
      if (isset($showcountarch) != $archives->showcount) {
        $archives->showcount = isset($showcountarch);
        $archives->Save();
      }
      
      $categories = &TCategories::instance();
      $categories->SetParams($categories->lite, $sortnamecats, isset($showcountcats), 0);
      
      $tags = &TTags::instance();
      $tags->SetParams($tags->lite, $sortnametags, isset($showcounttags), $maxcount);
      
      $posts = &TPosts::instance();
      $posts->recentcount = (int) $postscount;
      
      $comments = &TCommentManager::instance();
      $comments->recentcount = $commentscount;
      
      $links = &TLinksWidget::instance();
      $links->redir = isset($lwredir);
      $links->Save();
      
      $foaf = &TFoaf::instance();
      $foaf->SetParams($friendscount, $foafredir);
      
      $rname = 'stdoptsucces';
      break;
      
      case 'links':
      $links = TLinksWidget::instance();
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
      $widget = &TCustomWidget::instance();
      $id = $this->idget();
      if ($id > 0) {
        $widget->Edit($id, $title, $content, isset($templ));
      } elseif (!empty($title) || !empty($content)) {
        $widget->add($title, $content, isset($templ));
      }
      $rname = 'customsuccess';
      break;
    }
    
    $Urlmap->ClearCache();
    
    $html = &THtmlResource::instance();
    $html->section = $this->basename;
    $lang = &TLocal::instance();
  eval('$result = "'. $html->{$rname} . '\n";');
    return $result;
  }
  
  protected function DeleteWidgets() {
    global $Urlmap;
    $template = &TTemplate::instance();
    $template->Lock();
    $check = 'widgetcheck-';
    foreach ($_POST as $key => $value) {
      if ($check == substr($key, 0, strlen($check))){
        $id = (int) substr($key, strlen($check));
        $template->DeleteIdWidget($id);
      }
    }
    $template->Unlock();
    $Urlmap->ClearCache();
    $html = &THtmlResource::instance();
    $html->section = $this->basename;
    $lang = &TLocal::instance();
    
    eval('$result = "'. $html->successdeleted . '\n";');
    return $result;
  }
  
}//class
?>