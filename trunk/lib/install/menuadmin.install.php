<?php

function tadminmenuInstall($self) {
$self->lock();
//posts
$posts = $self->add(0, 'posts', 'author', 'tadminposts'); 
$self->add($posts, 'posts', 'author', 'tadminposts'); 


$self->unlock();
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>