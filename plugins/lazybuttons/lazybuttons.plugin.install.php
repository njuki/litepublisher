<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlazybuttonsInstall($self) {
$jsmerger = tjsmerger::instance();
$jsmerger->add('default', dirname(__file__) . 'lazybuttons.min.js');

    $parser = tthemeparser::instance();
    $parser->parsed = $this->themeparsed;
    ttheme::clearcache();
  }
  
function tlazybuttonsUninstall($self) {
$jsmerger = tjsmerger::instance();
$jsmerger->deletefile('default', dirname(__file__) . 'lazybuttons.min.js');
    
    $parser = tthemeparser::instance();
    $parser->unsubscribeclass($this);
    ttheme::clearcache();
  }
  