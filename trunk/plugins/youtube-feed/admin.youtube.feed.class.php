<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminyoutubefeed implements iadmin {

  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $feed = tyoutubefeed::i();
    $lang = tplugins::getlangabout(__file__);
    $args = new targs();
    $html = tadminhtml::i();
    if (!isset($_POST['step'])) $_POST['step'] = 1;
    switch ($_POST['step']) {
      case 2:
      case 3:
      $files = tfiles::i();
      $args->step = 3;
      $args->formtitle = $lang->feeditems;

      $tml = '<tr><td align="center"><input type="checkbox" name="youtubeid-$id" id="youtubeid-$id" value="$id" $checked /></td>
      <td><a href="http://www.youtube.com/watch?v=$id">$title</a></td></tr>';

      $items = '';
      foreach ($feed->items as $item) {
        $args->add($item);
        $args->checked = $files->exists($id) ? false : true;
        $items .= $html->parsearg($tml, $args);
      }

      $args->tablebody = $items;
      $args->tablehead = '<th align="center"><input type="checkbox" name="invertcheckbox" class="invertcheck" /></th>
      <th>Video</th>';

return $html->adminform(
$html->table($args) .
'[hidden:step]', $args);

      default:
      $args->step = 2;
      $args->formtitle = $lang->feedtitle;
      $args->url = $feed->url;
return $html->adminform('[text:url] [hidden:step]', $args);
    }
  }
  
  public function processform() {
    $feed = tyoutubefeed::i();
    switch ($_POST['step']) {
      case 2:
      $feed->url = trim($_POST['url']);
$feed->items= $feed->parsefeed($feed->url);
      $feed->save();
      break;
      
      case 3:
      foreach ($_POST as $k => $v) {
        if (strbegin($k, 'youtubeid-')) $feed->add($v);
        }
      }
      break;
    }
  }
  
}//class