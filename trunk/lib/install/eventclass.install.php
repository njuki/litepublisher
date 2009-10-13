<?php

function TEventClassInstall(&$self) {
  if(get_class($self) != 'TEventClass') return;
  if($self->dbversion) {
    $manager = TDBManager ::instance();
    $manager->CreateTable('posts', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'events.sql');
  }
}
?>