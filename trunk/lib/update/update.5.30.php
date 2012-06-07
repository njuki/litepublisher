<?php

function update530() {
litepublisher::$classes->data['memcache'] = false;
litepublisher::$classes->data['revision_memcache'] = 1;
litepublisher::$classes->add('tpullitems', 'items.pull.class.php');
litepublisher::$options->savemodified();

$lang = tlocal::i();
$js = tjsmerger::i();
$js->lock();
  $js->addtext('default', 'dialog', "var lang;\nif (lang == undefined) lang = {};\n" . sprintf('lang.dialog = %s;',  json_encode(
array(
  'error' => $lang->error,
  'confirm' => $lang->confirm,
'cancel' => $lang->cancel,
'yes' => $lang->yesword,
'no' => $lang->noword,
  )
)));
if (litepublisher::$classes->exists('tdownloaditems')) {
  $js->deletetext('default', 'downloaditem');
$js->add('default', '/plugins/downloaditem/downloaditem.min.js');
$view = tview::i();
$view->data['custom'] = array();
$view->save();
}

$js->unlock();

if (litepublisher::$classes->exists('tpolls')) {
$self = tpolls::i();
tposts::i()->unbind($self);
tcontentfilter::i()->unbind($self);
tcron::i()->deleteclass(get_class($self));

$man = tdbmanager::i();
$man->deletetable('pollusers');
$man->deletetable('pollvotes');
$man->query("rename table $man->polls to $man->oldpolls");

$self->install();

//import old polls
$man->setautoincrement($self->table, $man->getautoincrement('oldpolls'));
$db = $self->db;
$votestable = $db->prefix . $self->votes;
$id_tml = tpollsman::i()->pollpost;
    $from = 0;
    while ($a = $db->res2assoc($db->query("select * from $db->oldpolls order by id limit $from, 500"))) {
$from += count($a);
foreach ($a as $item) {
$idpoll = (int) $item['id'];
$total = 0;
    $votes = explode(',', $item['votes']);
$sum = 0;
foreach ($votes as $index => $count) {
$total += $count;
      $sum += ($index + 1) * $count;
        $db->exec("INSERT INTO $votestable (id, item, votes) values ($idpoll,$index,$count)");
}
$rate = $total == 0 ? 0 : (int) round($sum / $total * 10);
$self->db->insert_a(array(
'id' => $idpoll,
'id_tml' => $id_tml,
'total' => $total,
'rate' => $rate,
'status' => 'opened'
));
}
}

$man->deletetable('oldpolls');

if (litepublisher::$classes->exists('ttickets')) {
$self->db->update("status = 'closed'", "id in (
select poll from $db->tickets where $db->tickets.state = 'fixed'
)");
$man->alter('tickets', 'drop votes');
}
}
}