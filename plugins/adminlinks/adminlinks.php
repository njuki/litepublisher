<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincontextwidget extends torderwidget {
  
  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'widget.adminlinks';
$this->cache = 'nocache';
$this->adminclass = 'tadminorderwidget';
}

public function getdeftitle() {
$about = tplugins::getabout(tplugins::getname(__file__));
return $about['name'];
}

  public function getwidget($id, $sitebar) {
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('widget', $sitebar);
    tlocal::loadlang('admin');
    
    if (litepublisher::$urlmap->context instanceof tpost) {
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
      switch (get_class(litepublisher::$urlmap->context)) {
        case 'tcategories':
        case 'ttags':
        $tags = litepublisher::$urlmap->context;
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
        $links .= sprintf($tml, litepublisher::$options->url . "/admin/widgets/home/", tlocal::$data['widgets']['title']);
        break;
        
        default:
        if (litepublisher::$urlmap->context instanceof tmenu) {
          $menu = litepublisher::$urlmap->context;
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
    $links = sprintf($theme->getwidgetitems('widget', $sitebar), $links);
return $theme->getwidget($this->title, $links, 'widget', $sitebar);
  }
  
}//class

?>