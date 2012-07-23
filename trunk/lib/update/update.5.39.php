<?php

function update539() {
if (litepublisher::$classes->exists('tusernews')) {
$u = tusernews::i();
$u->data['dir'] = 'usernews';
    $u->data['checkspam'] = false;
    $u->data['editorfile'] = 'editor.htm';
$u->save();

tlocalmerger::i()->addplugin('usernews');
}
}