<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincommoncomments extends tadminmenu {
  protected $user;
  protected $showcolumns;
  
  protected function getmanager() {
    return litepublisher::$classes->commentmanager;
  }
  
  protected function create() {
    parent::create();
    $this->showcolumns = array();
    tfilestorage::loadvar(litepublisher::$paths->data . 'commentscolumns.php', $this->showcolumns);
  }
  
  protected function saveshowcolumns() {
    tfilestorage::savevar(litepublisher::$paths->data .'commentscolumns', $this->showcolumns);
  }
  
  protected function showcolumn($index, $default) {
    if (isset($_POST['changed_hidden'])) {
      $r = isset($_POST["checkbox-showcolumn-$index"]);
      $this->showcolumns[$index] =$r;
      return $r;
    } else {
      return isset($this->showcolumns[$index])? $this->showcolumns[$index] : $default;
    }
  }
  
  public function createtable() {
    $lang = tlocal::instance('comments');
    $table = new ttablecolumns();
    $table->index = 1;
    $table->checkboxes[]  = "<h4>$lang->showcolumns</h4><p>$lang->author: ";
    $table->add(
    '$id',
    'ID',
    'right',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$comment.date',
    $lang->date,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '$comment.localstatus',
    $lang->status,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '$comment.name',
    $lang->author,
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$email',
    'E-Mail',
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$website',
    $lang->website,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->checkboxes[] = "<br />$lang->content: ";
    $table->add(
    '<a href="$comment.url">$comment.posttitle</a>',
    $lang->post,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '$excerpt',
    $lang->content,
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$comment.ip',
    'IP',
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->checkboxes[]  = "<br />$lang->moderate: ";
    $table->add(
    '<a href="$adminurl=$comment.id&action=reply">$lang.reply</a>',
    $lang->reply,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=approve">$lang.approve</a>',
    $lang->approve,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=hold">$lang.hold</a>',
    $lang->hold,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=delete">$lang.delete</a>',
    $lang->delete,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=edit">$lang.edit</a>',
    $lang->edit,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->body ='<tr>
    <td align ="center"><input type="checkbox" name="checkbox-item-$id" id="checkbox-item-$id" value="$id" $onhold /></td>' .
    $table->body . '</tr>';
    
    $table->checkboxes[]  = '</p>';
    if (isset($_POST['changed_hidden'])) $this->saveshowcolumns();
    return $table;
  }
  
}//class