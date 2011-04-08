<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminredirector extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
$redir = tredirector::instance();
$html = $this->html;
$lang = $this->lang;
$args = targs::instance();
    $from = tadminhtml::getparam('from', '');
if (isset($redir->items[$from])) {
$args->from = $from;
$args->to = $redir->items[$from];
} else {
$args->from = '';
$args->to = '';
}
$args->formtitle= $lang->edit;
$result = $html->adminform('[text=from] [text=to]', $args);
    
$id = 1;
$items = array();
foreach ($redir->itms as $from => $to) {
$items[] = array(
'id' => $id++,
'from'  => $from,
'to' =>  $to
);
}

    $result .= $html->buildtable($items, array(
    array('center', '+', '<input type="checkbox" name="checkbox_$id" id="checkbox_$id" value="$from" />'),
    array('left', $lang->from, '<a href="$site.url$from" title="$from">$from</a>'),
    array('left', $lang->to,'<a href="$site.url$to" title="$to">$to</a>'),
    array('center', $lang->edit, "<a href=\"$adminurl=\$from\">$lang->edit</a>")
    ));

    $result = $html->fixquote($result);
return $result;
}

  public function processform() {
$redir = tredirector::instance();
if (isset($_POST['from'])) {
$redir->items[$_POST['from']] = $_POST['to'];
$redir->save();
return;
}

foreach ($_POST as $id => $value) {
if (strbegin($id, 'checkbox_')) {
if (isset($redir->items[$value])) unset($redir[$value);
}
}
$redir->save();
}

}//class

