<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlinksplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function onsitebar(&$content, $index) {
    if ($index > 0 || litepublisher::$urlmap->adminpanel) return;
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('widget', $index);
    tlocal::loadlang('admin');
    $template = ttemplate::instance();
    
    if ($template->context instanceof tpost) {
      $post = $template->context;
      $lang = tlocal::instance('posts');
      $title = $lang->adminpost;
      $editurl = litepublisher::$options->url . "/admin/posts/editor/" . litepublisher::$options->q . "id=$post->id";
      $action = litepublisher::$options->url . "/admin/posts/" . litepublisher::$options->q . "id=$post->id&action";
      $links = sprintf($tml, litepublisher::$options->url . "/admin/posts/editor/" . litepublisher::$options->q . "mode=short", tlocal::$data['names']['quick']);
      $links .= sprintf($tml, "$editurl&mode=short", $lang->edit);
      $links .= sprintf($tml, "$editurl&mode=midle", $lang->midledit);
      $links .= sprintf($tml, "$editurl&mode=full", $lang->fulledit);
      $links .= sprintf($tml, "$editurl&mode=update", $lang->updatepost);
      $links .= sprintf($tml, "$action=delete", $lang->delete);
    } else {
      switch (get_class($template->context)) {
        case 'tcategories':
        case 'ttags':
        $tags = $template->context;
        $name = $tags instanceof ttags ? 'tags' : 'categories';
        $adminurl = litepublisher::$options->url . "/admin/posts/$name/";
        $lang = tlocal::instance('tags');
      $title = $lang->{$name};
        $links = sprintf($tml,$adminurl, $lang->add);
        $adminurl .= litepublisher::$options->q . "id=$tags->id";
        $links .= sprintf($tml,$adminurl, $lang->edit);
        $links .= sprintf($tml, "$adminurl&action=delete", $lang->delete);
        $links .= sprintf($tml, "$adminurl&full=1", $lang->fulledit);
        break;
        
        case 'thomepage':
        $lang = tlocal::instance('options');
        $title = $lang->home;
        $links = sprintf($tml, litepublisher::$options->url . "/admin/options/home/", $lang->title);
        $links .= sprintf($tml, litepublisher::$options->url . "/admin/widgets/homepagewidgets/", tlocal::$data['names']['homepagewidgets']);
        break;
        
        default:
        if ($template->context instanceof tmenu) {
          $menu = $template->context;
          $lang = tlocal::instance('menu');
          $title = $lang->title;
          $adminurl = litepublisher::$options->url . "/admin/menu/edit/";
          $links = sprintf($tml,$adminurl, $lang->addmenu);
          $links .= sprintf($tml, $adminurl . litepublisher::$options->q . "id=$menu->id", $lang->edit);
        } else {
          return;
        }
        break;
      }
    }
    
    $links .= sprintf($tml, litepublisher::$options->url . "/admin/logout/", tlocal::$data['login']['logout']);
    $widget = $theme->getwidget($title, $links, 'widget', $index);
    $content = $widget . $content;
  }
  
}//class

?>