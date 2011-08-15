<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tclearcacheInstall($self) {
    litepublisher::$urlmap->beforerequest = $self->clearcache;
  $parser = tthemeparser::instance();
  $parser->parsed = $self->themeparsed;
  }
  
function tclearcacheUninstall($self) {
    litepublisher::$urlmap->unsubscribeclass($self);
  $parser = tthemeparser::instance();
  $parser->unsubscribeclass($self);
  }
  
