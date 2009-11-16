<?php

function TEventClassInstall(&$self) {
  if(get_class($self) != 'TEventClass') return;
  if(dbversion) {
    $manager = TDBManager ::instance();
    $manager->CreateTable('events', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'events.sql'));
  }
}

function TEventClassUninstall($self) {
global $options;
$options->delete($self->basename);
}
?>