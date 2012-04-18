<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttemplatecommentsInstall($self) {
tlocal::usefile('install');
$lang = tlocal::i('beforecommentsform');
$self->data['logged'] = sprintf('<p>%s</p>', sprintf($lang->logged,
 '<?php echo litepublisher::$site->getuserlink(); ?>', ' <a class="logout" href="$site.url/admin/logout/">' . $lang->logout . '</a> '));

$self->data['reqlogin'] = sprintf('<p>%s</p>', sprintf($lang->reqlogin,
 '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>'));

$self->data['guest'] = sprintf('<p>%s</p>', sprintf($lang->guest,
 '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>'));

$self->data['regaccount'] = sprintf('<p>%s</p>', sprintf($lang->regaccount,
'<a class="registration" href="$site.url/admin/reguser/{$site.q}backurl=">' . $lang->regaccount . '</a>'));

$self->data['comuser'] = sprintf('<p>%s</p>', sprintf($lang->comuser,
 '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>'));

$self->data['idgroups'] = tusergroups::i()->cleangroups('author, editor, moderator, ticket, commmentator');
$self->save();
}