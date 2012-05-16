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
$login = '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>';

  $self->data['logged'] = sprintf('<p>%s</p>', sprintf($lang->logged,
  '<?php echo litepublisher::$site->getuserlink(); ?>', ' <a class="logout" href="$site.url/admin/logout/">' . $lang->logout . '</a> '));
  
  $self->data['reqlogin'] = sprintf('<p>%s</p>', sprintf($lang->reqlogin,$login));
  
  $self->data['guest'] = sprintf('<p>%s</p>', sprintf($lang->guest, $login));
  
  $self->data['regaccount'] = sprintf('<p>%s</p>', sprintf($lang->regaccount,
'<a class="registration" href="$site.url/admin/reguser/{$site.q}backurl=">' . $lang->signup . '</a>'));
  
  $self->data['comuser'] = sprintf('<p>%s</p>', sprintf($lang->comuser, $login));

  $self->data['loadhold'] = sprintf('<h4>%s</h4>', sprintf($lang->loadhold,
'<a class="loadhold " href="$site.url/admin/comments/hold/">' . $lang->loadhold . '</a>'));
  
  $self->data['idgroups'] = tusergroups::i()->cleangroups('author, editor, moderator, ticket, commmentator');
  $self->save();
}