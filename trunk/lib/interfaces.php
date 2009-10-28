<?php
interface ITemplate {
  public function request($arg);
  public function gettitle();
  public function gethead();
  public function getkeywords();
  public function getdescription();
  public function GetTemplateContent();
}

interface IAdvancedTemplate {
public function getsitebar($index, &$content);
public function afterrequest(&$content);
}

interface  IComments {
//comment form
  public function gethold($author);
  public function IndexOfRawContent($s);
//holditems property used by TAdminModerator
  public function getholditems() {
//pingback xmlrpc
 public function haspingback($url);
}

?>