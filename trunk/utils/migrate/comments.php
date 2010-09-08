function migratecomments($idpost) {
global $data, $users;
$data->load('posts' . DIRECTORY_SEPARATOR  . $idpost . DIRECTORY_SEPARATOR . 'comments');
if (!isset($users) {
$users = new tmigratedata();
$users->load('commentusers');
}

$comments = tcomments::instance($idpost);
$comments->lock();
$comusers = tcomusers::instance($idpost);
$comusers->lock();
foreach ($data->data['items'] as $id => $item) {
if ($item['type'] == '') {
$user = $users->data['items'][$item['uid']];
$author = $comusers->add($user['name'], $user['email'], $user['url'], '');
$cid = $comments->add($author, $item['rawcontent'], $item['status'], $item['ip']);
if (dbversion) {
$comments->db->setvalue($cid, 'posted', sqldate($item['date']));
$comusers->db->settvalue($author, 'cookie', $user['cookie']);
} else {
$comments->items[$cid]['posted'] = $item['date'];
$comusers->items[$author]['cookie'] = $user['cookie'];
}
} else {
addpingback($idpost, $item);
}
}
$comusers->unlock();
$comments->unlock();
return $comments->count;
}

function addpingback($idpost, $item) {

}