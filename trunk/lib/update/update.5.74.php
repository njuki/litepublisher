<?php
function update574() {
if (litepublisher::$classes->exists('ulogin')) {
tcssmerger::i()->add('default', '/plugins/ulogin/ulogin.popup.css');
}
}