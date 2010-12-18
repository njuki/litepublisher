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
  public function getkeywords();
  public function getdescription();
  public function gethead();
  public function getcont();
  public function getidview();
  public function setidview($id);
}

interface iwidgets {
  public function getwidgets(array &$items, $sidebar);
  public function getsidebar(&$content, $sidebar);
}

interface iposts {
  public function add(tpost $post);
  public function edit(tpost $post);
  public function delete($id);
  public function deletedeleted($deleted);
}

?>