<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminmenusInstall($self) {
  $self->lock();
  //posts
  $posts = $self->createitem(0, 'posts', 'author', 'tadminposts');
  {
    //добавить еще одно подменю, но без добавления урла в карту урлов,
    $id = $self->createitem($posts, 'editor', 'author', 'tposteditor');
    $self->items[$id]['title'] = tlocal::$data['names']['midle'];
    $item = $self->items[$id];
    $item['id'] = ++$self->autoid;
    $item['url'] .= litepublisher::$options->q . 'mode=short';
    $item['title'] = tlocal::$data['names']['quick'];
    $self->items[$self->autoid] = $item;
    $self->createitem($posts, 'categories', 'editor', 'tadmintags');
    $self->createitem($posts, 'tags', 'editor', 'tadmintags');
    $self->createitem($posts, 'staticpages', 'editor', 'tadminstaticpages');
  }
  
  $moder = $self->createitem(0, 'comments', 'moderator', 'tadminmoderator');
  {
    $self->createitem($moder, 'hold', 'moderator', 'tadminmoderator');
if (dbversion) $self->createitem($moder, 'holdrss', 'moderator', 'tadmincommentsrss');
    $self->createitem($moder, 'pingback', 'moderator', 'tadminmoderator');
    $self->createitem($moder, 'authors', 'moderator', 'tadminmoderator');
  }
  
  $plugins = $self->createitem(0, 'plugins', 'admin', 'tadminplugins');
  $files = $self->createitem(0, 'files', 'author', 'tadminfiles');
  {
    $self->createitem($files, 'image', 'editor', 'tadminfiles');
    $self->createitem($files, 'video', 'editor', 'tadminfiles');
    $self->createitem($files, 'audio', 'editor', 'tadminfiles');
    $self->createitem($files, 'icon', 'editor', 'tadminfiles');
    $self->createitem($files, 'deficons', 'editor', 'tadminicons');
    $self->createitem($files, 'bin', 'editor', 'tadminfiles');
  }
  
  $widgets = $self->createitem(0, 'widgets', 'admin', 'tadminwidgets');
  {
    //$self->createitem($widgets, 'classes', 'admin', 'tadminwidgets');
    $self->createitem($widgets, 'home', 'admin', 'tadminwidgets');
    $self->createitem($widgets, 'addcustom', 'admin', 'tadminwidgets');
  }
  
  $themes = $self->createitem(0, 'themes', 'admin', 'tadminthemes');
  {
    $self->createitem($themes, 'edit', 'admin', 'tadminthemes');
    $self->createitem($themes, 'options', 'admin', 'tadminthemes');
  }
  
  $menu = $self->createitem(0, 'menu', 'editor', 'tadminmenumanager');
  {
    $id = $self->createitem($menu, 'edit', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::$data['menu']['addmenu'];
  }
  
  $foaf = $self->createitem(0, 'foaf', 'admin', 'tadminfoaf');
  {
    $self->createitem($foaf, 'profile', 'admin', 'tadminfoaf');
    $self->createitem($foaf, 'profiletemplate', 'admin', 'tadminfoaf');
  }
  
  $opt = $self->createitem(0, 'options', 'admin', 'tadminoptions');
  {
    $self->createitem($opt, 'home', 'admin', 'tadminoptions');
    $self->createitem($opt, 'mail', 'admin', 'tadminoptions');
    $self->createitem($opt, 'rss', 'admin', 'tadminoptions');
    $self->createitem($opt, 'view', 'admin', 'tadminoptions');
    $self->createitem($opt, 'comments', 'admin', 'tadminoptions');
    $self->createitem($opt, 'ping', 'admin', 'tadminoptions');
    $self->createitem($opt, 'links', 'admin', 'tadminoptions');
    $self->createitem($opt, 'openid', 'admin', 'tadminoptions');
    $self->createitem($opt, 'cache', 'admin', 'tadminoptions');
    $self->createitem($opt, 'lite', 'admin', 'tadminoptions');
    $self->createitem($opt, 'secure', 'admin', 'tadminoptions');
    $self->createitem($opt, 'robots', 'admin', 'tadminoptions');
    $self->createitem($opt, 'local', 'admin', 'tadminoptions');
    $self->createitem($opt, 'notfound404', 'admin', 'tadminoptions');
  }
  
  $service = $self->createitem(0, 'service', 'admin', 'tadminservice');
  {
    $self->createitem($service, 'backup', 'admin', 'tadminservice');
    $self->createitem($service, 'engine', 'admin', 'tadminservice');
    $self->createitem($service, 'run', 'admin', 'tadminservice');
  }
  
  $board = $self->additem(array(
  'parent' => 0,
  'url' => '/admin/',
  'title' => tlocal::$data['names']['board'],
  'name' => 'board',
  'class' => 'tadminboard',
  'group' => 'author'
  ));
  
  $self->unlock();
  
  $redir = tredirector::instance();
  $redir->add('/admin/', '/admin/posts/editor/');
}

function  tadminmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>