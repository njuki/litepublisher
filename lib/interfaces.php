<?php
interface ITemplate {
  public function request($arg);
  public function gettitle();
  public function gethead();
  public function getkeywords();
  public function getdescription();
  public function GetTemplateContent();
}

?>