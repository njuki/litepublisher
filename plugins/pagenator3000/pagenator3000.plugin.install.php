<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpagenator3000Install($self) {
    tcssmerger::i()->addstyle(dirname(__file__) . '/paginator3000.css');
$name = basename(dirname(__file__));
tjsmerger::i()->add('default', "/plugins/$name/paginator3000.min.js");

tthemeparser::i()->parsed = $self->themeparsed;
    ttheme::clearcache();
}

function tpagenator3000Uninstall($self) {
$name = basename(dirname(__file__));
tjsmerger::i()->deletefile('default', "/plugins/$name/paginator3000.min.js");

tthemeparser::i()->unbind($self);
    ttheme::clearcache();
    
    tcssmerger::i()->deletestyle(dirname(__file__) . '/paginator3000.css');
}