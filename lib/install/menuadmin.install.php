<?php

function tadminmenuInstall($self) {
$self->lock();
//posts
$posts = $self->add(0, 'posts', 'author', 'tadminposts'); 
{
$self->add($posts, 'editor', 'author', 'tposteditor'); 
$self->add($posts, 'categories', 'editor', 'tadmintags'); 
$self->add($posts, 'tags', 'editor', 'tadmintags'); 
}

$moder = $self->add(0, 'moderate', 'moderator', 'tadminmoderator'); 
{
$self->add($moder, 'hold', 'moderator', 'tadminmoderator'); 
$self->add($moder, 'pingback', 'moderator', 'tadminmoderator'); 
$self->add($moder, 'authors', 'moderator', 'tadminmoderator'); 
}

$menu = $self->add(0, 'menu', 'editor', 'tadminmenumanager'); 
{
$self->add($menu, 'edit', 'editor', 'tadminmenumanager'); 
}

$self->unlock();

$redir = tredirector::instance();
$redir->add('/admin/', '/admin/posts/editor/');
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>