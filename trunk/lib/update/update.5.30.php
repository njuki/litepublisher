<?php

function update530() {
if (litepublisher::$classes->exists('tpolls')) {
    tcssmerger::i()->addstyle(dirname(__file__) . '/stars.min.css');
  litepublisher::$urlmap->delete('/ajaxpollserver.htm');
$self = tpolls::i();
$self->templates['star'] = '<h4>$title</h4>
<ul id="pollform_$id" class="rating star_0">$items  </ul>';

$self->save();

  $json = tjsonserver::i();
  $json->addevent('polls_sendvote', get_class($self), 'polls_sendvote');

tlocalmerger::i()->add('polls', "plugins/polls/resource/" . litepublisher::$options->language . ".ini");

$name = 'polls';
litepublisher::$classes->add('tpollsfilter', 'polls.filter.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);

$man = tdbmanager::i();
$man->deletetable('pollusers');

}
}