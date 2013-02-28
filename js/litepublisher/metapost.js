/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Metapost = Class.extend({
  idpost: 0,
  items: {},
  
  init: function(idpost) {
  this.idpost = idpost;
  if (idpost > 0) this.load();
  },
  
  load: function() {
  var self = this;
        $.litejson({method: "get_metapost", idpost: this.idpost}, function(r){
        self.items = r;
        self.refresh();
        });
  },
  
  refresh: function(items) {
  var html = '';
  var tml = this.tml.item;
for (var name in this.items) {
html = html + $.simpletml(tml, {
name: name,
value: this.items[name]
});
}
this.holder.append(html);
  },
  
    });//class
}(jQuery, document, window));