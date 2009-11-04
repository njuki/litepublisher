<?php

function tadminmenuInstall($self) {
$self->lock();
//posts
$posts = $self->add(0, 'posts', 'author', 'tadminposts'); 
$self->add($posts, 'editor', 'author', 'tposteditor'); 
$self->add($posts, 'categories', 'editor', 'tadmincategories'); 
$self->add($posts, 'tags', 'editor', 'tadmincategories'); 

$self->unlock();
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>