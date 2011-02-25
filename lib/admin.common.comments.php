<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincommoncomments extends tadminmenu {
  protected $user;
  
  protected function getmanager() {
    return litepublisher::$classes->commentmanager;
  }

public function buildtable() {
$lang = tlocal::instance('comments');
$table = new ttablecolumns();
$table->add(
'$id', 
'ID',
'right',
true);

$table->add(
'$comment.date',
$lang->date,
'left',
true);
$table->add(
'$comment.localstatus',
$lang->status,
'left',
true);

$table->add(
'$comment.name',
$lang->author,
'left',
true);

$table->add(
'$email',
'E-Mail',
'left',
true);

$table->add(
'$website',
$lang->website,
'left',
true);

$table->add(
'<a href="$comment.url">$comment.posttitle</a>',
$lang->post,
'left',
true);

$table->add(
'$excerpt',
$lang->content,
'left',
true);

$table->add(
'$comment.ip',
'IP',
'left',
true);

$table->add(
<a href="$adminurl=$comment.id&action=reply">$lang.reply</a>',
$lang->reply,
'left',
false);

$table->add(
'<a href="$adminurl=$comment.id&action=approve">$lang.approve</a>',
$lang->approve,
'left',
false);

$table->add(
'<a href="$adminurl=$comment.id&action=hold">$lang.hold</a>',
$lang->hold,
'left',
false);

$table->add(
'<a href="$adminurl=$comment.id&action=delete">$lang.delete</a>',
$lang->delete,
'left',
false);

$table->add(
'<a href="$adminurl=$comment.id&action=edit">$lang.edit</a>',
$lang->edit,
'left',
false);

$table.body ='<tr>
<td align ="center"><input type="checkbox" name="checkbox-$id" id="checkbox-$id" value="$id" $onhold /></td>' .
$table.body . '</tr>';

return $table;
}
  
}//class