<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminmenusInstall($self) {
  $self->lock();
  $self->heads = '<link type="text/css" href="$site.files/js/jquery/jquery-ui-1.8.6.custom.css" rel="stylesheet" />
  <script type="text/javascript" src="$site.files/js/jquery/jquery-ui-1.8.6.custom.min.js"></script>
  <script type="text/javascript">
  $(document).ready(function() {
    $("input[rel=\'checkall\']").click(function() {
      $(this).closest("form").find("input:checkbox").attr("checked", true);
      $(this).attr("checked", false);
    });
    
    $("input[rel=\'invertcheck\']").click(function() {
      $(this).closest("form").find("input:checkbox").each(function() {
        $(this).attr("checked", ! $(this).attr("checked"));
      });
      $(this).attr("checked", false);
    });
    
  });
  </script>';
  
  //posts
  $posts = $self->createitem(0, 'posts', 'author', 'tadminposts');
  {
    $id = $self->createitem($posts, 'editor', 'author', 'tposteditor');
    $self->items[$id]['title'] = tlocal::$data['names']['newpost'];
    $self->createitem($posts, 'categories', 'editor', 'tadmintags');
    $self->createitem($posts, 'tags', 'editor', 'tadmintags');
    $self->createitem($posts, 'staticpages', 'editor', 'tadminstaticpages');
  }
  
  $moder = $self->createitem(0, 'comments', 'moderator', 'tadminmoderator');
  {
    $self->createitem($moder, 'hold', 'moderator', 'tadminmoderator');
    if (dbversion) $self->createitem($moder, 'holdrss', 'moderator', 'tadminmoderator');
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
  
  $views = $self->createitem(0, 'views', 'admin', 'tadminviews');
  {
    $self->createitem($views, 'themes', 'admin', 'tadminthemes');
    $self->createitem($views, 'themefiles', 'admin', 'tadminthemefiles');
    $self->createitem($views, 'widgets', 'admin', 'tadminwidgets');
    $self->createitem($views, 'addcustom', 'admin', 'tadminwidgets');
    $self->createitem($views, 'defaults', 'admin', 'tadminviews');
    $self->createitem($views, 'spec', 'admin', 'tadminviews');
    $self->createitem($views, 'headers', 'admin', 'tadminviews');
    $self->createitem($views, 'admin', 'admin', 'tadminviews');
  }
  
  $menu = $self->createitem(0, 'menu', 'editor', 'tadminmenumanager');
  {
    $id = $self->createitem($menu, 'edit', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::$data['menu']['addmenu'];
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
  
  /*
  $board = $self->additem(array(
  'parent' => 0,
  'url' => '/admin/',
  'title' => tlocal::$data['names']['board'],
  'name' => 'board',
  'class' => 'tadminboard',
  'group' => 'author'
  ));
  */
  $self->unlock();
  
  $redir = tredirector::instance();
  $redir->add('/admin/', '/admin/posts/editor/');
}

function  tadminmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>