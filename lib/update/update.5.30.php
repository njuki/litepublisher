<?php

function update530() {
litepublisher::$classes->data['memcache'] = false;
litepublisher::$classes->data['revision_memcache'] = 1;
litepublisher::$classes->add('tpullitems', 'items.pull.class.php');

if (litepublisher::$classes->exists('tpolls')) {
$data = new tdata();
$data->basename = 'plugins'. DIRECTORY_SEPARATOR . 'tpolls';
$data->load();

$res = litepublisher::$paths->plugins . 'polls' . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    tcssmerger::i()->addstyle(dirname(__file__) . '/stars.min.css');
  litepublisher::$urlmap->delete('/ajaxpollserver.htm');
$self = tpolls::i();
$self->templates['star'] = '<h4>$title</h4>
<ul id="pollform_$id" class="rating star_0">$items  </ul>';

$self->save();

tposts::i()->unbind($self);
tcontentfilter::i()->unbind($self);
tcron::i()->deleteclass(get_class($self));

  $json = tjsonserver::i();
  $json->addevent('polls_sendvote', get_class($self), 'polls_sendvote');

tlocalmerger::i()->add('polls', "plugins/polls/resource/" . litepublisher::$options->language . ".ini");

$name = 'polls';
litepublisher::$classes->add('tpoltypes', 'poll.types.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);
litepublisher::$classes->add('tadminpolltemplates', 'admin.poll.templates.php', $name);
litepublisher::$classes->add('tadminpolltypes', 'admin.poll.types.php', $name);
litepublisher::$classes->add('tadminpolloptions', 'admin.polloptions.class.php', $name);

$man = tdbmanager::i();
$man->deletetable('pollusers');
$man->alter('pollvotes', 'drop vote');
$man->query("rename table $db->pollvotes to $db->pollusers1");
  $man->createtable($self->users2, file_get_contents($res . 'users.sql'));
  $man->createtable($self->votes, file_get_contents($res . 'votes.sql'));
$man->alter($self->table, "drop index hash");
$man->alter($self->table, "drop hash");
  $man->alter($self->table, "add total int UNSIGNED NOT NULL default 0 after id");
  $man->alter($self->table, "add KEY total(total)");

$db = litepublisher::$db;
$votestable = $db->prefix . $self->votes;
$db->table = $self->table;
    $from = 0;
    while ($a = $db->res2assoc($db->query("select id, votes from $self->thistable limit $from, 500"))) {
$from += count($a);
foreach ($a as $item) {
$idpoll = (int) $item['id'];
$total = 0;
    $votes = explode(',', $item['votes']);
foreach ($votes as $index => $count) {
$total += $count;
        $db->exec("INSERT INTO $votestable (id, item, votes) values $idpoll,$i,$count");
}
$self->db->setvalue($idpoll, 'total', $total);
}
}
$man->alter($self->table, "drop votes");
}
}