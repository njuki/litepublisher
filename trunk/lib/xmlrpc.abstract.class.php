<?php

class TXMLRPCAbstract extends TEventClass {
  public $error;
  
  public function uninstall() {
    $aller = TXMLRPC::instance();
    $caller->deleteclass(get_class($this));
  }
  
  public function canlogin(&$args, $LoginIndex = 1) {
    global $options;
    if (!$options->auth($args[$LoginIndex], $args[$LoginIndex + 1])) {
      $this->error = new IXR_Error(403, 'Bad login/pass combination.');
      return false;
    }
    return true;
  }
  
}

?>