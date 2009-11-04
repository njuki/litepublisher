<?php

global $classes;
return is_a($instance, $classes->classes['tags']);
}

class tadmincategories extends tadminmenuitem {

  public static function instance() {
    return getinstance(__class__);
  }

private function gettags() {
global $classes;
if (isset($_GET['class']) && ($_GET['class'] == 'tag')){
return $classes->tags;
} else {
return $classes->categories;
}
}
  
  public function getcontent() {
    global $options, $classes;
$result = '';
$tags = $this->gettags();
$html = $this->html;
$h2 = $html->h2;
    $id = $this->idget();
$args = new targs();
$args->id = $id;
$args->class = istags($tags) ? 'tag' : 'cat';
    if ($id ==  0) {
      $args->title = '';
$result .= istags($tags) ? $h2->addtag : $h2->addcategory;
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

$result .= istags($tags) ? $h2->edittag : $h2->editcategory;
    if (isset($_GET['full'])) {        
        $args->url = $item['url'];
$args->keywords = $tags->contents->getkeywords($id);
$args->description = $tags->contents->getdescription($id);
$args->content =$tags->contents->getcontent($id);
$result .= $html->fullform($args);
      } else {
      $result = $html->{ form($args);
}
    }
    
    //table
$result .= $html->listhead();
    foreach ($tags->getitems() as $item) {
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
$tags = $this->gettags();    
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

function istags($instance) {
?>