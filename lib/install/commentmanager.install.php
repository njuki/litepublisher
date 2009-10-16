<?php

function TCommentManagerInstall(&$self) {
$self->options = array('recentcount' =>  7,
'SendNotification' =>  true);

  $Posts= tposts::instance();
  $Posts->deleted = $self->PostDeleted;
}

function TCommentManagerUninstall(&$self) {
  tposts::unsub($self);
  
  $template = ttemplate::instance();
  $template->DeleteWidget(get_class($self));
}

?>