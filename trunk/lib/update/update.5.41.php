<?php

function update541() {
tposts::i()->changed = thomepage::i()->postschanged;
thomepage::i()->postschanged();

if (litepublisher::$classes->exists('tregservices')) }
tregservices::i()->update_widget();
$css = tcssmerger::i();
$css->addstyle('/plugins/regservices/regservice.min.css');
}
}