<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class adminitems  {
  public static function getcontent($holder, $menu) {
    $result = '';
    $html = $menu->html;
    $lang = tlocal::admin();
    $id = (int) tadminhtml::getparam('id', 0);
    $args = new targs();
    $args->id = $id;
    $args->adminurl = $menu->adminurl;

        if (isset($_GET['action']) && ($_GET['action'] == 'delete') && $tags->itemexists($id)) {
      if  (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
        $holder->delete($id);
        $result .= $html->h4->deleted;
      } else {
        return $html->confirmdelete($id, $menu->adminurl, $lang->confirmdelete);
      }
    }
    
    if ($id ==  0) {

    } elseif ($holder->itemexists($id)) {
      $item = $holder->getitem($id);
      $args->add($item);

      $result .= $html->adminform($menu->editform, $args);
    }
    
    //table
    $perpage = 20;
    $count = $holder->count;
    $from = $menu->getfrom($perpage, $count);
    
    $items = array_slice($tags->items, $from, $perpage);
    foreach ($items as &$item) {
      $item['parentname'] = $parents[$item['parent']];
    }
    $result .= $html->buildtable($items, array(
    array('right', $lang->count2, '$itemscount'),
    array('left', $lang->parent, '$parentname'),
    array('right', $lang->order, '$customorder'),
    array('left', $lang->title,'<a href="$link" title="$title">$title</a>'),
    array('center', $lang->edit, "<a href=\"$this->adminurl=\$id\">$lang->edit</a>"),
    array('center', $lang->delete, "<a href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>")
    ));
    $result = $html->fixquote($result);
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  private function set_view(array $item) {
    extract($_POST, EXTR_SKIP);
    $item['idview'] = (int) $idview;
    $item['includechilds'] = isset($includechilds);
    $item['includeparents'] = isset($includeparents);
    $item['invertorder'] = isset($invertorder);
    $item['lite'] = isset($lite);
    $item['liteperpage'] = (int) trim($liteperpage);
    if (isset($idperm)) $item['idperm'] = (int) $idperm;
    if (isset($icon)) $item['icon'] = (int) $icon;
    return $item;
  }
  
  public function processform() {
    if (empty($_POST['title'])) return '';
    extract($_POST, EXTR_SKIP);
    $istags = $this->name == 'tags';
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    $tags->lock();
    $id = $this->idget();
    if ($id == 0) {
      $id = $tags->add((int) $parent, $title);
      if (isset($order)) $tags->setvalue($id, 'customorder', (int) $order);
      if (isset($url)) $tags->edit($id, $title, $url);
      if (isset($idview)) {
        $item =$tags->getitem($id);
        $item = $this->set_view($item);
        $tags->items[$id] = $item;
        $item['id'] = $id;
        unset($item['url']);
        if ($tags->dbversion) $tags->db->updateassoc($item);
      }
    } else {
      $item = $tags->getitem($id);
      $item['title'] = $title;
      if (isset($parent)) $item['parent'] = (int) $parent;
      if (isset($order)) $item['customorder'] = (int) $order;
      if (isset($idview)) $item = $this->set_view($item);
      $tags->items[$id] = $item;
      if (!empty($url) && ($url != $item['url'])) $tags->edit($id, $title, $url);
      $tags->items[$id] = $item;
      if (dbversion) {
        unset($item['url']);
        $tags->db->updateassoc($item);
      }
    }
    
    if (isset($raw) || isset($keywords)) {
      $item = $tags->contents->getitem($id);
      if (isset($raw)) {
        $filter = tcontentfilter::i();
        $item['rawcontent'] = $raw;
        $item['content'] = $filter->filter($raw);
      }
      if (isset($keywords)) {
        $item['keywords'] = $keywords;
        $item['description'] = $description;
        $item['head'] = $head;
      }
      $tags->contents->setitem($id, $item);
    }
    
    $tags->unlock();
    return sprintf($this->html->h2->success, $title);
  }
  
}//class


?>