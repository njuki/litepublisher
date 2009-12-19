<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminwidgets extends tadminmenu {

  public static function instance() {
    return getinstance(__class__);
  }
  
  private function getcombosortname ($comboname, $sortname) {
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
          $result = "<select name='$name' id='$name'>\n";
        for ($i = 1; $i <= $count; $i++) {
          $selected = $i == $index  ? 'selected' : '';
          $result .= "<option $selected>$i</option>\n";
        }
$result .= "</select>\n";
return $result;
}

private function getwidgettitle($id) {
$widgets = twidgets::instance();
          $widget = $widgets->getitem($id);
if (!empty($widget['title'])) return $widget['title'];
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
$html = $this->html;
$args = targs::instance();    

    switch ($this->name) {
      case 'widgets':
      $result = $html->checkallscript;
$result .= $html->formhead();
// принимается что макс число сайтбаров = 3
      for ($i = 0; $i < 3; $i++) {
$j = 0;
foreach ($widgets->items[$i] as $id => $item) {
          $args->id = $id;
$args->title = $this->getwidgettitle($id);
          $args->sitebarcombo = $this->getcombo("sitebar-$id", $i, 3);
          $args->ordercombo = $this->getcombo("order-$id", $j++, $widgets->getcount($i));
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

      $args->showcountcats = $classes->categories->showcount;
      $args->catscombo= $this->getcombosortname('sortnamecats', $classes->categories->sortname);
      
      $args->showcounttags = $classes->tags->showcount;
$args->maxcount = $classes->tags->maxcount;
      $args->tagscombo= $this->getcombosortname('sortnametags', $classes->tags->sortname);

$args->postscount = $classes->posts->recentcount;
$commentswidget  = tcommentswidget::instance();
$args->commentscount = $commentswidget->recentcount;

      $links = tlinkswidget::instance();
      $args->linksredir = $links->redir;
      
      $foaf = tfoaf::instance();
      $args->foafredir = $foaf->redir;
      $result = $html->stdoptionsform($args);
      break;
      
      case 'links':
      $links = tlinkswidget::instance();
$result =  $html->h2->linkswidget;
$result .=  $html->p->linksnote;
      $id = $this->idget();
      if ($id > 0) {
        $args->add($links->items[$id]);
        $result .= sprintf($html->h3->editlink, $links->items[$id]['url']);
      } else {
        $args->url = '';
        $args->title = '';
        $args->text = '';
$result .= $html->h3->newlink;
      }
$result .= $html->linkform($args);
      
      $result .= $html->checkallscript;
$result .= $html->linkstable();
$args->adminurl = $this->adminurl;
      foreach ($links->items as $id => $item) {
$args->id = $id;
$args->url = $item['url'];
$args->text = $item['text'];
$args->title = $item['title'];
$result .= $html->linkitem($args);
      }
$result .= $html->linkstablefooter();
      return $this->FixCheckall($result);
      
      case 'custom':
      $custom = tcustomwidget::instance();
$result = $html->h2->custom . $html->p->custnote;

      $id = $this->idget();
      if ($id > 0) {
        $args->title = $custom->items[$id]['title'];
        $args->content = $custom->items[$id]['content'];
        $args->templ = $custom->items[$id]['templ'];
        $result .= sprintf($html->h3->editcustom, $args->title);
      } else {
        $args->title = '';
        $args->content = '';
        $args->templ = true;
        $result .= $html->h3->newcustom;
      }
      $result .= $html->customform($args);

      $result .= "<ul>\n";
$args->adminurl = $this->adminurl;
      foreach ($custom->items as $id => $item) {
$args->id = $id;
$args->title = $item['title'];
$args->text = $item['text'];
      $result .= $html->customitem($args);
$result .= "\n";
      }
      $result .= "</ul>\n";
      break;
    }
    
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function processform() {
    global $classes, $options, $urlmap;
    $urlmap->clearcache();
$widgets = twidgets::instance();
$h2 = $this->html->h2;
    switch ($this->name) {
      case 'widgets':
      if (!empty($_POST['deletewidgets'])) {
        return $this->DeleteWidgets();
      }

      $widgets->lock();
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
return $h2->success;

      case 'std':
//подготовить массив с именами виджетов
      array_pop($_POST);
$names = array();
foreach ($_POST as $name=> $value) {
if (strbegin($name, 'ajax-')) {
$name = substr($name, strlen('ajax-'));
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
$std->add($name, $names[$name], 0);
}
}
$std->updateajax();
$std->unlock();
$widgets->unlock();
return $h2->stdsuccess;
    
      case 'stdoptions':
      extract($_POST);
      
      $archives = tarchives::instance();
      if (isset($showcountarch) != $archives->showcount) {
        $archives->showcount = isset($showcountarch);
        $archives->Save();
      }

$options->lock();

$classes->categories->sortname = $sortnamecats;
$classes->categories->showcount = isset($showcountcats);
$classes->categories->save();

$classes->tags->sortname = $sortnametags;
$classes->tags->showcount = isset($showcounttags);
$classes->tags->maxcount = maxcount;
$classes->tags->save();

$classes->posts->recentcount = $postscount;
$classes->posts->save();

$commentswidget = tcommentswidget::instance();
$commentswidget->recentcount = $commentscount;

      $links = tlinkswidget::instance();
$links->redir = isset($linksredir);
$links->save();
      
      $foaf = tfoaf::instance();
$foaf->redir = isset($foafredir);
$options->unlock();
      return $h2->stdoptsucces;
     
      case 'links':
      $links = tlinkswidget::instance();
      if (!empty($_POST['delete'])) {
      $links->lock();
        foreach ($_POST as $id => $value) {
          if ($links->itemexists($id)) {
            $links->delete($id);
          }
        }
      $links->unlock();
return $h2->linksdeleted;
      }

        extract($_POST);
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($links->itemexists($id)) {
          $links->edit($id, $url, $title, $text);
        } else {
          $links->add($url, $title, $text);
        }
return $h2->linkedited;

      case 'custom':
      extract($_POST);
      $custom = tcustomwidget::instance();
      $id = $this->idget();
      if ($id > 0) {
        $custom->edit($id, $title, $content, isset($templ));
      } elseif (!empty($title) || !empty($content)) {
        $custom->add($title, $content, isset($templ));
      }
return $h2->customsuccess;
}
}
 
  protected function DeleteWidgets() {
    global $urlmap;
$template = ttemplate::instance();
$template->lock();
$widgets = twidgets::instance();
    $widgets->lock();
    $check = 'widgetcheck-';
    foreach ($_POST as $key => $value) {
      if (strbegin($key, $check)){
        $id = (int) substr($key, strlen($check));
$widgets->delete($id);
      }
    }
    $widgets->unlock();
$template->unlock();
    $urlmap->clearcache();
   return $this->html->h2->successdeleted;
  }
  
}//class
?>