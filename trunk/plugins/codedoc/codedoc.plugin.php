<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocplugin extends tplugin {
  private $fix;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'codedoc';
    $this->fix = array();
  }
  
  public function beforefilter($post, $content) {
    $content = trim($content);
    //if (!strbegin($content, '[document]')) return;
if (!preg_match('/^\s*codedoc\s*[=:]\s*(class|interface|manual)/', $content, $m)) return;
    $filter = tcodedocfilter::i();
    $result = $filter->convert($post, $content, $m[1]);
    if ($post->id == 0) {
      $result['post'] = $post;
      $this->fix[] = $result;
    } else {
      $result['id'] = $post->id;
      $this->db->updateassoc($result);
    }
    tevents::cancelevent(true);
  }
  
  public function postadded($id) {
    if (count($this->fix) == 0) return;
    foreach ($this->fix as $i => $item) {
      if ($id == $item['post']->id) {
        $post = $item['post'];
        $this->db->add(array(
        'id' => $id,
        'parent' => $item['parent'],
        'class' => $item['class']
        ));
        
        $filter = tcodedocfilter::i();
        $filtered = str_replace('__childs__', $filter->getchilds($post->id), $post->filtered);
        
        $posts = tposts::i();
        $posts->addrevision();
        
        $post->db->updateassoc(array(
        'id' => $post->id,
        'filtered' => $filtered,
        'revision' => $posts->revision
        ));
        unset($this->fix[$i]);
        return;
      }
    }
  }
  
}//class