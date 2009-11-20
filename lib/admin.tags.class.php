<?php

class tadmintags extends tadminmenuitem {

  public static function instance() {
    return getinstance(__class__);
  }

  public function getcontent() {
    global $options, $classes;
$result = '';
$istags = $this->name == 'tags';
$tags = $istags  ? $classes->tags : $classes->categories;
$html = $this->html;
$h2 = $html->h2;
    $id = $this->idget();
$args = targs::instance();
$args->id = $id;
$args->adminurl = $this->adminurl;
    if ($id ==  0) {
      $args->title = '';
$result .= $istags ? $h2->addtag : $h2->addcategory;
$result .= $html->tagform($args);
    } elseif (!$tags->ItemExists($id)) {
return $this->notfound;
    } else {
$item = $tags->getitem($id);
      $args->title = $item['title'];

      if (isset($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $tags->delete($id);
return $h2->successdeleted;
        } else {
return $html->confirmdelete($args);
}
        }

$result .= $istags ? $h2->edittag : $h2->editcategory;
    if (isset($_GET['full'])) {        
        $args->url = $item['url'];
$args->keywords = $tags->contents->getkeywords($id);
$args->description = $tags->contents->getdescription($id);
$args->content =$tags->contents->getcontent($id);
$result .= $html->fullform($args);
      } else {
      $result = $html->form($args);
}
    }
    
    //table
$result .= $html->listhead();
    foreach ($tags->getallitems() as $item) {
$args->id = $itm['id'];
$args->title = $item['title'];
$args->url = $item['url'];
$args->count = $item['itemscount'];
$result .= $html->itemlist($args);
    }
$result .= $html->listfooter;
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function processform() {
    global $options, $classes;
    if (empty($_POST['title'])) return '';
extract($_POST);
$istags = $this->name == 'tags';
$tags = $istags  ? $classes->tags : $classes->categories;
        $id = $this->idget();
    if ($id == 0) {
      $id = $tags->add($title);
      } elseif (isset($_GET['full'])) {
        $tags->edit($id, $title, $url);
$tags->contents->edit($id, $content, $description, $keywords);
      } else {
        $tags->edit($id, $title, $tags->geturl($id));
      }

    return sprintf($this->html->h2->success, $title);
  }
  
}//class


?>