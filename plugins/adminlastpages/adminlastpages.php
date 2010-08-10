<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlastpages extends torderwidget {
public $links;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.adminlastpages';
    $this->cache = 'nocache';
    $this->adminclass = 'tadminorderwidget';
$this->addmap('links', array());
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
      $post = litepublisher::$urlmap->context;
      $lang = tlocal::instance('posts');
      $title = $lang->adminpost;
      $editurl = litepublisher::$options->url . "/admin/posts/editor/" . litepublisher::$options->q . "id=$post->id";
      $action = litepublisher::$options->url . "/admin/posts/" . litepublisher::$options->q . "id=$post->id&action";
      $links = $this->getitem($tml, "/admin/posts/editor/" . litepublisher::$options->q . "mode=short", tlocal::$data['names']['quick']);
      $links .= $this->getitem($tml, "$editurl&mode=short", $lang->edit);
      $links .= $this->getitem($tml, "$editurl&mode=midle", $lang->midledit);
      $links .= $this->getitem($tml, "$editurl&mode=full", $lang->fulledit);
      $links .= $this->getitem($tml, "$editurl&mode=update", $lang->updatepost);
      $links .= $this->getitem($tml, "$action=delete", $lang->delete);
    } else {
      switch (get_class(litepublisher::$urlmap->context)) {
        case 'tcategories':
        case 'ttags':
        $tags = litepublisher::$urlmap->context;
        $name = $tags instanceof ttags ? 'tags' : 'categories';
        $adminurl = litepublisher::$options->url . "/admin/posts/$name/";
        $lang = tlocal::instance('tags');
      $title = $lang->{$name};
        $links = $this->getitem($tml,$adminurl, $lang->add);
        $adminurl .= litepublisher::$options->q . "id=$tags->id";
        $links .= $this->getitem($tml,$adminurl, $lang->edit);
        $links .= $this->getitem($tml, "$adminurl&action=delete", $lang->delete);
        $links .= $this->getitem($tml, "$adminurl&full=1", $lang->fulledit);
        break;
        
        case 'thomepage':
        $lang = tlocal::instance('options');
        $title = $lang->home;
        $links = $this->getitem($tml, "/admin/options/home/", $lang->title);
        $links .= $this->getitem($tml, "/admin/widgets/home/", tlocal::$data['widgets']['title']);
        break;
        
        default:
        if ((litepublisher::$urlmap->context instanceof tmenu) && !(litepublisher::$urlmap->context instanceof tadminmenu)) {
          $menu = litepublisher::$urlmap->context;
          $lang = tlocal::instance('menu');
          $title = $lang->title;
          $adminurl = litepublisher::$options->url . "/admin/menu/edit/";
          $links = $this->getitem($tml,$adminurl, $lang->addmenu);
          $links .= $this->getitem($tml, $adminurl . litepublisher::$options->q . "id=$menu->id", $lang->edit);
        } else {
          return;
        }
        break;
      }
    }
    
    $links .= $this->getitem($tml, '/admin/logout/', tlocal::$data['login']['logout']);
    $links = $theme->getwidgetcontent($links, 'widget', $sitebar);
    return $theme->getwidget($this->gettitle($id), $links, 'widget', $sitebar);
  }
private function getitem($tml, $url, $title) {
$args = targs::instance();
$args->icon = '';$args->subitems = '';
$args->rel = 'admin';
if (strbegin($url, 'http://')) {
$args->url = $url;
} else {
$args->url = litepublisher::$options->url  . $url;
}
$args->title = $title;
$args->anchor = $title;
$theme = ttheme::instance();
return $theme->parsearg($tml, $args);
}
  
}//class

?>