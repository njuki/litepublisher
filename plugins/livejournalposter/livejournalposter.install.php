<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlivejournalInstall($self) {
  $posts = tposts::instance();
  $posts->singlecron = $self->sendpost;
}

function tlivejournalUninstall($self) {
  tposts::unsub($self);
  if (dbversion) {
    //litepublisher::$db->table = 'postsmeta';
    //litepublisher::$db->delete("name = 'ljid'");
  }
}

?>