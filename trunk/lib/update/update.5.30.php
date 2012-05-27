<?php

function update530() {
if (litepublisher::$classes->exists('tpolls')) {
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
litepublisher::$classes->add('tpollsfilter', 'polls.filter.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);

$man = tdbmanager::i();
$man->deletetable('pollusers');
$man->alter('pollvotes', 'drop vote');
  $man->createtable($self->voted2, file_get_contents($res . 'votes.sql'));
$man->alter($self->table, "drop index hash");
$man->alter($self->table, "drop hash");
}
}