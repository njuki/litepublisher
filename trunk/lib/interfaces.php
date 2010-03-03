<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

interface itemplate {
  public function request($arg);
  public function gettitle();
  public function gethead();
  public function getkeywords();
  public function getdescription();
  public function GetTemplateContent();
  // property theme;
  // property $tmlfile;
}

interface itemplate2 {
  public function getsitebar();
  public function afterrequest(&$content);
}

interface imenu {
  public function getparent();
  public function setparent($id);
  public function getorder();
  public function setorder($order);
}

interface iposts {
public function add(tpost $post);
public function edit(tpost $post);
public function delete($id);
public function deletedeleted($deleted);
}

?>