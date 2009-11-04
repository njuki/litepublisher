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

$menu = $self->add(0, 'menu', 'editor', 'tadminmenumanager'); 
{
$self->add($menu, 'edit', 'editor', 'tadminmenumanager'); 
}
$self->unlock();
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>