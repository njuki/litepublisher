<?php
function update391() {
if (!dbversion) return;
$backup = array (
  'urlmap' => 
  array (
    'events' => 
    array (
      'onclearcache' => 
      array (
        0 => 
        array (
          'class' => 'twidgetscache',
          'func' => 'onclearcache',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
  ),
  'posts' => 
  array (
    'events' => 
    array (
      'changed' => 
      array (
        0 => 
        array (
          'class' => 'tarchives',
          'func' => 'postschanged',
        ),
      ),
      'deleted' => 
      array (
        0 => 
        array (
          'class' => 'tcommentmanager',
          'func' => 'postdeleted',
        ),
        1 => 
        array (
          'class' => 'tsubscribers',
          'func' => 'deletepost',
        ),
        2 => 
        array (
          'class' => 'tpingbacks',
          'func' => 'postdeleted',
        ),
        3 => 
        array (
          'class' => 'tcategories',
          'func' => 'postdeleted',
        ),
        4 => 
        array (
          'class' => 'ttags',
          'func' => 'postdeleted',
        ),
        5 => 
        array (
          'class' => 'tfileitems',
          'func' => 'deletepost',
        ),
      ),
      'added' => 
      array (
        0 => 
        array (
          'class' => 'tcategories',
          'func' => 'postedited',
        ),
        1 => 
        array (
          'class' => 'ttags',
          'func' => 'postedited',
        ),
        2 => 
        array (
          'class' => 'tfiles',
          'func' => 'postedited',
        ),
      ),
      'edited' => 
      array (
        0 => 
        array (
          'class' => 'tcategories',
          'func' => 'postedited',
        ),
        1 => 
        array (
          'class' => 'ttags',
          'func' => 'postedited',
        ),
        2 => 
        array (
          'class' => 'tfiles',
          'func' => 'postedited',
        ),
      ),
      'singlecron' => 
      array (
        0 => 
        array (
          'class' => 'tpinger',
          'func' => 'pingpost',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
    'archivescount' => '1',
    'revision' => 0,
    'itemcoclasses' => 
    array (
    ),
  ),
  'comments' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'comusers' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'pingbacks' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  '' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
  ),
  'categories' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
    'defaultid' => 0,
  ),
  'tags' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
  ),
  'filesitemsposts' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'files' => 
  array (
    'events' => 
    array (
      'deleted' => 
      array (
        0 => 
        array (
          'class' => 'ticons',
          'func' => 'filedeleted',
        ),
        1 => 
        array (
          'class' => 'tdownloadcounter',
          'func' => 'delete',
        ),
      ),
      'changed' => 
      array (
        0 => 
        array (
          'class' => 'trssMultimedia',
          'func' => 'fileschanged',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
  ),
  'users' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
);

$storage = &litepublisher::$options->data['storage'];
foreach ($backup as $name => $data) {
if (isset($storage[$name])) {
if (count($storage[$name]['events']) == 0) $storage[$name]['events'] = $data['events'];
} else {
$storage[$name] = $data;
}
}

litepublisher::$options->save();
litepublisher::$options->savemodified();
}
?>