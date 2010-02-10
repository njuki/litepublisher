<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminmenusInstall($self) {
  $self->lock();
  $board = $self->additem(array(
  'parent' => 0,
  'url' => '/admin/',
  'title' => tlocal::$data['names']['board'],
  'name' => 'board',
  'class' => 'tadminboard',
  'group' => 'author'
  ));
  
  //posts
  $posts = $self->add(0, 'posts', 'author', 'tadminposts');
  {
    //добавить еще одно подменю, но без добавления урла в карту урлов,
    $id = $self->add($posts, 'editor', 'author', 'tposteditor');
    $self->items[$id]['title'] = tlocal::$data['names']['midle'];
    $item = $self->items[$id];
    $item['id'] = ++$self->autoid;
    $item['url'] .= litepublisher::$options->q . 'mode=short';
    $item['title'] = tlocal::$data['names']['quick'];
    $self->items[$self->autoid] = $item;
    $self->add($posts, 'categories', 'editor', 'tadmintags');
    $self->add($posts, 'tags', 'editor', 'tadmintags');
  }
  
  $moder = $self->add(0, 'comments', 'moderator', 'tadminmoderator');
  {
    $self->add($moder, 'hold', 'moderator', 'tadminmoderator');
    $self->add($moder, 'pingback', 'moderator', 'tadminmoderator');
    $self->add($moder, 'authors', 'moderator', 'tadminmoderator');
  }
  
  $plugins = $self->add(0, 'plugins', 'admin', 'tadminplugins');
  $files = $self->add(0, 'files', 'author', 'tadminfiles');
  {
    $self->add($files, 'image', 'editor', 'tadminfiles');
    $self->add($files, 'video', 'editor', 'tadminfiles');
    $self->add($files, 'audio', 'editor', 'tadminfiles');
    $self->add($files, 'icon', 'editor', 'tadminfiles');
    $self->add($files, 'bin', 'editor', 'tadminfiles');
  }
  
  $widgets = $self->add(0, 'widgets', 'admin', 'tadminwidgets');
  {
    $self->add($widgets, 'std', 'admin', 'tadminwidgets');
    $self->add($widgets, 'stdoptions', 'admin', 'tadminwidgets');
    $self->add($widgets, 'links', 'admin', 'tadminwidgets');
    $self->add($widgets, 'custom', 'admin', 'tadminwidgets');
    $self->add($widgets, 'meta', 'admin', 'tadminwidgets');
    $self->add($widgets, 'homepagewidgets', 'admin', 'tadminwidgets');
  }
  
  $themes = $self->add(0, 'themes', 'admin', 'tadminthemes');
  {
    $self->add($themes, 'edit', 'admin', 'tadminthemes');
  }
  
  $menu = $self->add(0, 'menu', 'editor', 'tadminmenumanager');
  {
    $id = $self->add($menu, 'edit', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::$data['menu']['addmenu'];
  }
  
  $opt = $self->add(0, 'options', 'admin', 'tadminoptions');
  {
    $self->add($opt, 'home', 'admin', 'tadminoptions');
    $self->add($opt, 'mail', 'admin', 'tadminoptions');
    $self->add($opt, 'rss', 'admin', 'tadminoptions');
    $self->add($opt, 'view', 'admin', 'tadminoptions');
    $self->add($opt, 'comments', 'admin', 'tadminoptions');
    $self->add($opt, 'ping', 'admin', 'tadminoptions');
    $self->add($opt, 'links', 'admin', 'tadminoptions');
    $self->add($opt, 'openid', 'admin', 'tadminoptions');
    $self->add($opt, 'cache', 'admin', 'tadminoptions');
    $self->add($opt, 'lite', 'admin', 'tadminoptions');
    $self->add($opt, 'secure', 'admin', 'tadminoptions');
    $self->add($opt, 'robots', 'admin', 'tadminoptions');
    $self->add($opt, 'local', 'admin', 'tadminoptions');
    $self->add($opt, 'notfound404', 'admin', 'tadminoptions');
  }
  
  $service = $self->add(0, 'service', 'admin', 'tadminservice');
  {
    $self->add($service, 'backup', 'admin', 'tadminservice');
    $self->add($service, 'engine', 'admin', 'tadminservice');
    $self->add($service, 'run', 'admin', 'tadminservice');
  }
  
  $self->unlock();
  
  $redir = tredirector::instance();
  $redir->add('/admin/', '/admin/posts/editor/');
}

function  tadminmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>