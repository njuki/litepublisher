<?php

function update540() {
$item = array(
'id' => 1,
    'email' =>litepublisher::$options->email,
    'name' => litepublisher::$site->author,
    'website' => litepublisher::$site->url . '/',
    'password' => litepublisher::$options->password,
    'cookie' => litepublisher::$options->cookie,
    'expired' => sqldate(litepublisher::$options->cookieexpired ),
    'status' => 'approved',
    'idgroups' => '1',
    );

$users = tusers::i();
if ($users->db->idexists(1)) {
$users->db->updateassoc($item);
} else {
$users->db->insert_a($item);
}

  $users->setgroups(1, array(1));
}