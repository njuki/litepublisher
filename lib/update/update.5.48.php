<?php

function update548() {
litepublisher::$site->jquery_version = '1.9.0';
  litepublisher::$site->jqueryui_version = '1.10.1';
  litepublisher::$site->save();
  
  $t = ttemplate::i();
  $t-footer = str_replace('2012', '2013', $t-footer);
  $t->save();
  
  $admin = tadminmenus::i();
  $admin->lock();
        $id = $admin->createitem($admin->url2id('/admin/options/'), 'files', 'admin', 'tadminoptions');
        $admin->items[$id]['order'] = $admin->items[$admin->url2id('/admin/options/view/')]['order'];
        $admin->sort();
          $admin->unlock();
          
          $m = tmediaparser::i();
          $m->data['quality_snapshot'] = 95;
 $m->data['quality_original'] = 95;

          $m->save();
}