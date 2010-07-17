<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminwidgets extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getcombosortname ($comboname, $sortname) {
return self::getcombo(tlocal::$data['sortnametags'], $comboname, $sortname) {
  }
  
  public static function getcombo(array $items, $name, $index) {
    $result = "<select name='$name' id='$name'>\n";
foreach ($items as $i => $item) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $i, $i == $index  ? 'selected' : '', $item);
    }
    $result .= "</select>\n";
    return $result;
  }


public static function getsitebarnames($count) {
$result = range(1, $count );
$parser = tthemeparser::instance();
$template = ttemplate::instance();
$about = $parser->getabout($template->theme);
foreach ($result as $key => $value) {
if (isset($about["sitebar$value"])) $result[$i] = $about['sitebar$value"];
}
return $result;
}
  
public static function getsitebarsform(array $sitebars) {
$widgets = twidgets::instance();
    $args = targs::instance();
    $html = THtmlResource ::instance();
$html->section = 'widgets';
    $result = $html->checkallscript;
    $result .= $html->formhead();
$count = count($sitebars);
$sitebarnames = self::getsitebarnames(count($sitebars));
foreach ($sitebars as $i => $sitebar)
$orders = range(1, count($sitebar));
foreach ($sitebar as $j => $_item) {
$id = $_item['id'];
        $args->id = $id;
        $args->add($widgets->getitem($id));
        $args->sitebarcombo = $this->getcombo($sitebarnames, "sitebar-$id", $i);
        $args->ordercombo = $this->getcombo($orders, "order-$id", $j);
        $result .= $html->item($args);
      }
    }
    $result .= $html->formfooter();
    return  $html->fixquote($result);
  }
  
// parse POST into sitebars array
  public static function editsitebars(array $sitebars) {
    // collect all id from checkboxes
    $items = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-'))$items[] = (int) $value;
    }
    
    foreach ($items as $id) {
    if (isset($_POST['deletewidgets']))  {
if ($pos = tsitebars::getpos($sitebars, $id)) {
list($i, $j) = $pos;
array_delete($sitebars[$i], $j);
}
} else {
$i = (int)$_POST["sitebar-$id"];
$j = (int) $_POST["order-$id"];
tsitebars::setpos($sitebars, $id, $i, $j);
    }
}

return $sitebars;
    return $this->html->h2->success;
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $args = targs::instance();
    
    switch ($this->name) {
      case 'widgets':
      return $this->getwidgets(twidgets::instance());
      
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
      
      $args->showcountcats = litepublisher::$classes->categories->showcount;
      $args->catscombo= $this->getcombosortname('sortnamecats', litepublisher::$classes->categories->sortname);
      
      $args->showcounttags = litepublisher::$classes->tags->showcount;
      $args->maxcount = litepublisher::$classes->tags->maxcount;
      $args->tagscombo= $this->getcombosortname('sortnametags', litepublisher::$classes->tags->sortname);
      
      $args->postscount = litepublisher::$classes->posts->recentcount;
      $manager = tcommentmanager::instance();
      $args->commentscount = $manager->recentcount;
      
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
      return $html->fixquote($result);
      
      case 'custom':
      $custom = tcustomwidget::instance();
      $result = $html->h2->custom . $html->p->custnote;
      
      $id = $this->idget();
      if ($id > 0) {
        $args->add($custom->items[$id]);
        $result .= sprintf($html->h3->editcustom, $custom->items[$id]['title']);
      } else {
        $args->title = '';
        $args->content = '';
        $args->templ = true;
        $result .= $html->h3->newcustom;
      }
      $result .= $html->customform($args);
      
      $list = '';
      $args->adminurl = $this->adminurl;
      foreach ($custom->items as $id => $item) {
        $args->id = $id;
        $args->add($item);
        $list .= $html->customitem($args);
      }
      $result .= sprintf("<ul>\n%s\n</ul>\n", $list);
      break;
      
      case 'meta':
      $std = tstdwidgets::instance();
      foreach ($std->meta as $name => $value) $args->$name = $value;
      
      $result .= $html->metaform($args);
      break;
      
      case 'homepagewidgets':
      $home = thomepage::instance();
      $args->defaultswidgets = $home->defaultswidgets;
      $args->showstandartcontent = $home->showstandartcontent;
      $result .= $html->homepageoptions($args);
      $result = str_replace("'", '"', $result);
      if (!$home->defaultswidgets) {
        $result .= $this->getwidgets(twidgets::instance('homepage'));
      }
      return $result;
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function processform() {
    litepublisher::$urlmap->clearcache();
    $widgets = twidgets::instance();
    $h2 = $this->html->h2;
    
    switch ($this->name) {
      case 'widgets':
      return $this->setwidgets(twidgets::instance());
      
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
      
      litepublisher::$options->lock();
      
      litepublisher::$classes->categories->sortname = $sortnamecats;
      litepublisher::$classes->categories->showcount = isset($showcountcats);
      litepublisher::$classes->categories->save();
      
      $tags = litepublisher::$classes->tags;
      $tags->sortname = $sortnametags;
      $tags->showcount = isset($showcounttags);
      $tags->maxcount = (int) $maxcount;
      $tags->save();
      
      litepublisher::$classes->posts->recentcount = (int) $postscount;
      litepublisher::$classes->posts->save();
      $manager = tcommentmanager::instance();
      $manager->recentcount = (int) $commentscount;
      $manager->save();
      
      $links = tlinkswidget::instance();
      $links->redir = isset($linksredir);
      $links->save();
      
      $foaf = tfoaf::instance();
      $foaf->redir = isset($foafredir);
      $foaf->maxcount =(int) $friendscount;
      litepublisher::$options->unlock();
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
      
      case 'meta':
      $std = tstdwidgets::instance();
      $std = tstdwidgets::instance();
      foreach ($std->meta as $name => $value) $std->data['meta'][$name] = isset($_POST[$name]);
      $std->save();
      return $h2->metasuccess;
      
      case 'homepagewidgets':
      if (isset($_POST['homepageoptions'])) {
        extract($_POST);
        $home = thomepage::instance();
        $home->lock();
        $home->defaultswidgets = isset($defaultswidgets);
        $home->showstandartcontent = isset($showstandartcontent);
        $home->unlock();
      } else {
        return $this->setwidgets(twidgets::instance('homepage'));
      }
    }//switch
  }
  

}//class
?>