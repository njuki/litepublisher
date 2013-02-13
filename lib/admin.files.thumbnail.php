<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfilethumbnails extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getidfile() {
    $files = tfiles::i();
    $id = $this->idget();
    if (($id == 0) || !$files->itemexists($id)) return false;
    if (litepublisher::$options->hasgroup('editor')) return $id;
    $user = litepublisher::$options->user;
    $item = $files->getitem($id);
    if ($user == $item['author']) return $id;
    return false;
  }
  
  public function getcontent() {
    if (!($id = $this->getidfile()))   return $this->notfound;
    $result = '';
    $files = tfiles::i();
    $html = $this->html;
    $args = new targs();
    $args->adminurl = $this->adminurl;
    $item = $files->getitem($id);
    $idpreview = $item['preview'];
    if ($idpreview > 0) {
      $args->add($files->getitem($idpreview));
      $args->idfile = $id;
      $result .= $html->preview($args);
    }
    
    $args->id = $id;
    $result .= $html->uploadthumb($args);
    return $result;
  }
  
  public function processform() {
    if (!($id = $this->getidfile()))   return $this->notfound;
    $files = tfiles::i();
    $item = $files->getitem($id);
    
    if (isset($_POST['submitdelete'])) {
      $files->delete($item['preview']);
      $files->setvalue($id, 'preview', 0);
      return $this->html->h4->deleted;
    }
    
    $isauthor = 'author' == litepublisher::$options->group;
    if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
      $error = tlocal::get('uploaderrors', $_FILES["filename"]["error"]);
      return "<h3>$error</h3>\n";
    }
    
    if (!is_uploaded_file($_FILES['filename']['tmp_name'])) return sprintf($this->html->h4->attack, $_FILES["filename"]["name"]);
    if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
    
    $filename = $_FILES['filename']['name'];
    $tempfilename = $_FILES['filename']['tmp_name'];
    $parser = tmediaparser::i();
    $filename = tmediaparser::linkgen($filename);
    $parts = pathinfo($filename);
    $newtemp = $parser->gettempname($parts);
    if (!move_uploaded_file($tempfilename, litepublisher::$paths->files . $newtemp)) return sprintf($this->html->h4->attack, $_FILES["filename"]["name"]);
    
    //addfile($filename, $newtemp, $title, $description, $keywords, $overwrite);
    $tempfilename = $newtemp;
    $hash =$files->gethash(litepublisher::$paths->files . $tempfilename);
    if (($idpreview = $files->IndexOf('hash', $hash)) ||
    ($idpreview = $files->getdb('imghashes')->findid('hash = '. dbquote($hash)))) {
      @unlink(litepublisher::$paths->files . $tempfilename);
      return ;
    }
    
    $info = $parser->getinfo($tempfilename);
    if ($info['media'] != 'image') {
      @unlink(litepublisher::$paths->files . $tempfilename);
      return ;
    }
    
    $info['filename'] = $parser->movetofolder($filename, $tempfilename, $parser->getmediafolder($info['media']), false);
    
    $newitem = $info + array(
    'filename' => $filename,
    'parent' => $id,
    'preview' => 0,
    'title' => $filename,
    'description' => '',
    'keywords' => ''
    );
    
    if (isset($_POST['noresize'])) {
      $idpreview = $files->additem($newitem);
    } else {
      $srcfilename = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $info['filename']);
      if (($source = tmediaparser::readimage($srcfilename)) && tmediaparser::createthumb($source, $srcfilename, $parser->previewwidth, $parser->previewheight, $parser->ratio, $parser->clipbounds, $parser->quality_snapshot)) {
        @chmod($srcfilename, 0666);
        $imginfo = getimagesize($srcfilename);
        $newitem['media'] = 'image';
        $newitem['mime'] = $imginfo['mime'];
        $newitem['width'] = $imginfo[0];
        $newitem['height'] = $imginfo[1];
        
        $idpreview = $files->additem($newitem);
        $files->getdb('imghashes')->insert(array(
        'id' => $idpreview,
        'hash' => $hash
        ));
      } else return;
    }
    
    if ($item['preview'] > 0) $files->delete($item['preview']);
    $files->setvalue($id, 'preview', $idpreview);
    $files->setvalue($idpreview, 'parent', $id);
    if ($item['idperm'] > 0) {
      $files->setvalue($idpreview, 'idperm', $item['idperm']);
      tprivatefiles::i()->setperm($idpreview, (int) $item['idperm']);
    }
    return $this->html->h4->success;
  }
  
}//class