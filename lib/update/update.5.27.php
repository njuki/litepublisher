<?php

function update527() {
litepublisher::$options->delete('autocmtform');
litepublisher::$options->delete('commentsenabled');

$cm = tcommentmanager::i();
$data = new tdata();
$data->basename = 'commentmanager';
$data->load();
$cm->data = $data->data;
    $cm->data['canedit'] =  true;
    $cm->data['candelete'] =  true;
$cm->data['confirmemail'] = false;
$cm->data['confirmlogged'] = false;
$cm->data['confirmguest'] = true;
$cm->data['confirmcomuser'] = true;

  $cm->data['comuser_subscribe'] = true;

    $cm->data['idguest'] =  tusers::i()->add(array(
'email' => '',
'name' => tlocal::get('default', 'guest'),
'status' => 'approved',
'idgroups' => 'commentator'
));

$cm->data['idgroups'] = tusergroups::i()->cleangroups('admin, editor, moderator, author, commentator, ticket');

$spam = new tdata();
$spam->basename = 'spamfilter';
$spam->load();
if (isset($spam->data['events'])) {
foreach ($spam->data['events'] as $eventname => $events) {
if ($eventname == 'onstatus') $eventname = 'oncreatestatus';
$cm->data['events'][$eventname] = $events;
}
}

unset($cm->data['events']['approved']);
unset($cm->data['events']['added']);
$cm->save();
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/comusers.htm'), 'class', 'tcommentmanager');

litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/reguser/'), 'type', 'get');
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/logout/'), 'type', 'get');
  tposts::unsub($cm);
  tposts::i()->addevent('deleted', 'tcomments', 'postdeleted');
  tposts::i()->addevent('added', 'tsubscribers', 'postadded');

$comments = tcomments::i();
$comments->lock();
$comments->changed = $cm->changed;
$comments->added = $cm->sendmail;

$subscribers = tsubscribers::i();
  $comments->added = $subscribers->sendmail;
  $comments->onapproved = $subscribers->sendmail;

$comments->changed = tcommentswidget::i()->changed;

$comments->changed = trss::i()->commentschanged;
$comments->changed = trssholdcomments::i()->commentschanged;

$comments->unlock();

tusers::i()->deleted = $subscribers->deleteitem;

$classes = litepublisher::$classes;
$classes->delete('tjsonserver');
$classes->add('tjsonserver', 'jsonserver.class.php');
$classes->delete('tjsoncomments');
$classes->add('tjsoncomments', 'json.comments.class.php');
$classes->add('Tadmincommentmanager', 'admin.commentmanager.class.php');
$classes->add('tadmincomusers', 'admin.comments.users.class.php');
$classes->add('tadminpingbacks', 'admin.pingbacks.class.php');
$classes->add('tsession', 'session.class.php');

$classes->items['tusers'][0] = 'kernel.php';
$classes->items['tusergroups'][0] = 'kernel.php';

$classes->delete('tuseroptions');
$classes->add('tuseroptions', 'user.options.class.php');

if ($classes->exists('tregservice')) {
  $classes->add('toauth', 'oauth.class.php', 'regservices');
  $classes->add('ttwitterregservice', 'twitter.service.php', 'regservices');
$reg = tregservices::i();
$reg->add(ttwitterregservice::i());
$man = tdbmanager::i();
$man->alter('regservices', "drop index service");
$man->addenum('regservices', 'service', 'twitter');
$man->alter('regservices', "add KEY (`service`, `uid`)");

tcommentform::i()->oncomuser = $reg->oncomuser;
}

unset($classes->items['tspamfilter']);
unset($classes->classes['spamfilter']);
unset($classes->items['tkeptcomments']);
unset($classes->items['tcomusers']);
unset($classes->classes['comusers']);

$classes->save();

  litepublisher::$options->comstatus = 'guest';

$admin = tadminmenus::i();
if ($id = $admin->url2id('/admin/options/comments/')) {
$admin->items[$id]['class'] = 'Tadmincommentmanager';
litepublisher::$urlmap->setvalue($admin->items[$id]['idurl'], 'class', 'Tadmincommentmanager');
}

if ($id = $admin->url2id('/admin/comments/pingback/')) {
$admin->items[$id]['class'] = 'tadminpingbacks';
litepublisher::$urlmap->setvalue($admin->items[$id]['idurl'], 'class', 'tadminpingbacks');
}

if ($id = $admin->url2id('/admin/comments/authors/')) {
$admin->items[$id]['class'] = 'tadmincomusers';
litepublisher::$urlmap->setvalue($admin->items[$id]['idurl'], 'class', 'tadmincomusers');
}

$admin->deleteurl('/admin/comments/holdrss/');

if ($id = $admin->url2id('/admin/comments/hold/')) {
$admin->items[$id]['group'] = 'commentator';
}

if ($id = $admin->url2id('/admin/comments/')) {
$admin->items[$id]['group'] = 'commentator';
}

$admin->save();

$lang = tlocal::admin();
$js = tjsmerger::i();
$js->lock();
  $js->add('default', '/js/litepublisher/prettyphoto.dialog.min.js');
  $js->addtext('default', 'dialog', "var lang;\nif (lang == undefined) lang = {};\n" . sprintf('lang.dialog = %s;',  json_encode(
array(
  'error' => $lang->error,
  'confirm' => $lang->confirm
  )
)));

    $js->add('comments', '/js/litepublisher/confirmcomment.min.js');
  $js->add('comments', '/js/litepublisher/moderate.min.js');
include_once(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR  . 'jsmerger.class.install.php');
set_comments_lang($js);

tplugins::i()->delete('ajaxcommentform');
unset($js->items['moderate']);
$js->unlock();

$template = ttemplate::i();
if (isset($template->jsmerger_moderate)) {
@unlink(litepublisher::$paths->home . ltrim($template->jsmerger_moderate, '/'));
unset($template->data['jsmerger_moderate']);
$template->save();
}

  tcssmerger::i()->add('default', '/js/litepublisher/prettyphoto.dialog.css');

ttemplatecomments::i()->install();

$db = litepublisher::$db;
$db->table = 'users';
$db->insertrow($db->assoctorow(array(
'id' =>1,
    'email' =>litepublisher::$options->email,
    'name' => litepublisher::$site->author,
    'website' => litepublisher::$site->url . '/',
    'password' => litepublisher::$options->password,
    'cookie' => litepublisher::$options->cookie,
    'expired' => sqldate(litepublisher::$options->cookieexpired ),
    'status' => 'approved',
    'idgroups' => '1',
)));

$db->table = 'usergroup';
        $db->add(array(
        'iduser' => '1',
        'idgroup' => 1
        ));

$man = tdbmanager::i();
$man->delete_enum('users', 'status', 'lock');
$man->delete_enum('users', 'status', 'wait');
$man->addenum('users', 'status', 'comuser');

$man->addenum('urlmap', 'type', 'usernormal');
$man->addenum('urlmap', 'type', 'userget');

$man->alter('posts', "add `comstatus` enum('closed','reg','guest','comuser') default 'comuser'");

$db->table = 'posts';
$db->update("comstatus = 'closed'", "commentsenabled = 0");
$man->alter('posts', "drop commentsenabled");

$groups = tusergroups::i();
if ($idgroup = $groups->getidgroup('subscriber')) {
$groups->items[$idgroup]['name'] = 'commentator';
$groups->save();
} else {
$idgroup = $groups->getidgroup('commentator');
}

$man->alter('comments', "add tmp int unsigned NOT NULL default '0'");

// $map for subscribers
$map = array();
    $from = 0;
$db->table = 'comusers';
    while ($items = $db->res2assoc($db->query("select * from $db->comusers limit $from, 100"))) {
$from += count($items);
      foreach ($items as $item) {
$db->table = 'users';
if ($id = $db->findid('email = '. dbquote($item['email']))) {
$u = $db->getitem($id);
//echo $item['id'], ' ', $item['email'], ' ', $item['name'], '<br>';
//echo "found $id ", $u['email'], ' ', $u['name'], '<br>';
} else {
$id = $db->add(array(
    'email' => $item['email'],
    'name' =>$item['name'],
    'website' => $item['url'],
    'password' => '',
    'cookie' =>  $item['cookie'],
    'expired' => sqldate(),
    'idgroups' => "$idgroup",
    'trust' => $item['trust'],
    'status' => 'comuser',
));

//echo "added $id ", $item['name'], ' ', $item['email'], '<br>';
}

        $db->query("update $db->comments set tmp = '$id' where author= '" . $item['id'] . "'");
$map[(int) $item['id']] = (int) $id;
      }

$db->table = 'comusers';
    }

//create temp table
$man->deletetable('tempsubscribers');
$man->createtable('tempsubscribers', file_get_contents(litepublisher::$paths->lib . 'install' .DIRECTORY_SEPARATOR . 'items.posts.sql'));
    $from = 0;
$db->table = 'subscribers';
    while ($items = $db->res2assoc($db->query("select * from $db->subscribers limit $from, 500"))) {
$from += count($items);
$db->table = 'tempsubscribers';
      foreach ($items as $item) {
$idpost = (int) $item['post'];
$idold = (int) $item['item'];
$idnew = $map[$idold];
if (!$db->finditem("post = $idpost and  item = $idnew")) {
        $db->exec("INSERT INTO $db->tempsubscribers (post, item) values ($idpost, $idnew)");
}
        }

$db->table = 'subscribers';
}

$man->deletetable('subscribers');
$db->query("rename table $db->tempsubscribers to $db->subscribers");

//move column
$man->alter('comments', "drop index author");
$man->alter('comments', "drop author");
$man->alter('comments', "change tmp author int unsigned NOT NULL default '0'");
$man->alter('comments', "add KEY `author` (`author`)");

$man->deletetable('comusers');
$man->deletetable('commentskept');

tcron::i()->deleteclass('tusers');

if ($classes->exists('tticket')) {
$tickets = ttickets::i();
$tickets->data['idcomauthor'] =  tusers::i()->add(array(
'email' => '',
'name' => tlocal::get('ticket', 'comname'),
'status' => 'approved',
'idgroups' => 'commentator'
));
$tickets->save();
}

litepublisher::$options->savemodified();
}