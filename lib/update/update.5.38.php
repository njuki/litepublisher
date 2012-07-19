<?php

function update538() {
$lang = tlocal::admin('editor');

$cm = tcommentmanager::i();
$users = tusers::i();
if (($cm->idguest == 0) || !$users->itemexists($cm->idguest)) {
    $cm->idguest =  $users->add(array(
    'email' => '',
    'name' => tlocal::get('default', 'guest'),
    'status' => 'hold',
    'idgroups' => 'commentator'
    ));
    $cm->save();
    $users->setvalue($cm->idguest, 'status', 'approved');
    } else {
$user = $users->getitem($cm->idguest);
if ($user['name'] == '') {
    $users->setvalue($cm->idguest, 'name', tlocal::get('default', 'guest'));
}
}

$js = tjsmerger::i();
$js->lock();
  $section = 'default';
  //$js->add($section, '/js/plugins/class-extend.min.js');
$s = implode("\n", $js->items[$section]['files']);
$p = 'jquery.prettyPhoto.js';
$s = str_replace($p, $p . "\n/js/plugins/class-extend.min.js", $s);
$js->setfiles($section, $s);

if (litepublisher::$classes->exists('tpagenator3000')) {
$js->add($section, '/plugins/pagenator3000/paginator3000.min.js');
$about = tplugins::getabout('pagenator3000');
  $js->addtext('default', 'pagenator', 
sprintf('var lang = $.extend(true, lang, { pagenator: %s });',
  json_encode(array(
                'next' =>  $about['next'],
                'last' => $about['last'],
                'prior' => $about['prior'],
                'first' => $about['first']
))));
}
$js->unlock();
}