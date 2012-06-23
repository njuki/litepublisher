<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function set_comments_lang($self) {
  $lang = tlocal::admin('comments');
  $self->addtext('comments', 'lang',
  sprintf('var lang = $.extend(true, lang, {
    comment: %s,
    comments: %s
  });',
  json_encode($lang->ini['comment']),
  json_encode(array(
  'del' => $lang->delete,
  'edit' => $lang->edit,
  'approve' => $lang->approve,
  'hold' => $lang->hold,
  'confirmdelete' => $lang->confirmdelete,
  'yesdelete' => $lang->yesdelete,
  'nodelete' => $lang->nodelete,
  'notdeleted' => $lang->notdeleted,
  'notmoderated' => $lang->notmoderated,
  'errorrecieved' => $lang->errorrecieved,
  'notedited' => $lang->notedited,
  ))
  ));
}

function tjsmergerInstall($self) {
  $dir = litepublisher::$paths->files . 'js';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
  $file = $dir . DIRECTORY_SEPARATOR . 'index.htm';
  file_put_contents($file, ' ');
  @chmod($file, 0666);
  
  $self->lock();
  $self->items = array();
  $section = 'default';
  $self->add($section, '/js/jquery/jquery-$site.jquery_version.min.js');
  $self->add($section, '/js/prettyphoto/js/jquery.prettyPhoto.js');
  $self->add($section, '/js/litepublisher/cookie.min.js');
  $self->add($section, '/js/litepublisher/litepublisher.utils.min.js');
  $self->add($section, '/js/litepublisher/widgets.min.js');
  $self->add($section, '/js/litepublisher/players.min.js');
  $self->add($section, '/js/litepublisher/prettyphoto.dialog.min.js');
  $self->addtext($section, 'pretty',
  '$(document).ready(function() {
    $("a[rel^=\'prettyPhoto\']").prettyPhoto({
      social_tools: false
    });
  });');
  
  $section = 'comments';
  $self->add($section, '/js/litepublisher/comments.min.js');
  $self->add($section, '/js/litepublisher/confirmcomment.min.js');
  $self->add($section, '/js/litepublisher/moderate.min.js');
  set_comments_lang($self);
  
  tlocal::usefile('admin');
$js = "var lang;\nif (lang == undefined) lang = {};\n";
  $widgetlang = array(
  'expand' => tlocal::get('default', 'expand'),
  'colapse' => tlocal::get('default', 'colapse')
  );
  $lang = tlocal::admin();
  $self->addtext('default', 'widgetlang', $js . sprintf('lang.widgetlang= %s;',  json_encode($widgetlang)));
  $self->addtext('default', 'dialog', $js . sprintf('lang.dialog = %s;',  json_encode(
  array(
  'error' => $lang->error,
  'confirm' => $lang->confirm,
  'confirmdelete' => $lang->confirmdelete,
  'cancel' => $lang->cancel,
  'yes' => $lang->yesword,
  'no' => $lang->noword,
  )
  )));
  
  $section = 'admin';
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
  $self->add($section, '/js/litepublisher/admin.min.js');

  $section = 'adminviews';
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.mouse.min.js');
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.draggable.min.js');
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.droppable.min.js');
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.selectable.min.js');
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.sortable.min.js');
  $self->add($section, '/js/litepublisher/admin.views.min.js');

  $section = 'posteditor';
  $self->add($section, '/js/swfupload/swfupload.js');
  $self->add($section, '/js/litepublisher/swfuploader.min.js');
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.progressbar.min.js');
  $self->add($section, '/js/litepublisher/simpletml.min.js');
  $self->add($section, '/js/litepublisher/posteditor.min.js');
  $self->add($section, '/js/litepublisher/fileman.min.js');
  $self->add($section, '/js/litepublisher/fileman.templates.min.js');
  
  $lang =tlocal::admin('editor');
  $self->addtext($section, 'lang', $js . sprintf('lang.posteditor= %s;',  json_encode(
  array(
  'emptytitle' => $lang->emptytitle,
  'upload' => $lang->upload,
  'currentfiles' => $lang->currentfiles,
  'newupload' => $lang->newupload,
  'add' => $lang->add,
  'del' => $lang->delete,
  'property' => $lang->property,
  'title' => $lang->title,
  'description' => $lang->description,
  'keywords' => $lang->keywords,'file' => $lang->file,
  'filesize' => $lang->filesize,
  )
  )));
  
  $self->unlock();
  
  $template = ttemplate::i();
  $template->addtohead(sprintf($template->js, '$site.files$template.jsmerger_default'));
  
  $updater = tupdater::i();
  $updater->onupdated = $self->onupdated;
}

function tjsmergerUninstall($self) {
  tupdater::i()->unbind($self);
}