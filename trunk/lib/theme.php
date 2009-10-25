<?php

class t

class theme extends TEventClass {
public static function instance($name) {
$result = getinstance(__class__);
$result->loadtheme($name);
return $result;
}

}//class
?>