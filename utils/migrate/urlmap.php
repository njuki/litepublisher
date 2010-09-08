function addurl($url, $obj, $id) {
return litepublisher::$urlmap->add($url, get_class($obj), $id, 'normal');
}

