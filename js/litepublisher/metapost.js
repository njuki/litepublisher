/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Metapost = Class.extend({
    items: {},
  idpost: 0,
  holder: false,

    init: function(idpost, owner) {
  this.idpost = idpost;
  this.holder = $($.simpletml(litepubl.tml.metapost.holder, {
  lang: lang.posteditor
  }).appendTo(owner);
  
  $(litepubl.tml.metapost.body, this.holder).on("click.metapost", function() {
  
  return false;
  });
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
  var tml = litepubl.tml.metapost.item;
for (var name in this.items) {
html = html + $.simpletml(tml, {
name: name,
value: this.items[name]
});
}
$(litepubl.tml.metapost.body, this.holder).append(html);
  },
  
    });//class
    
    litepubl.tml.metapost = {
  item: '<tr id="metapost-%%name%%"><td class="propname">%%name%%</td><td class="propvalue">%%value%%</td>' +
  '<td class="propdelete file-toolbar"><a href="#" title="%%lang.del%%" class="delete-toolbutton"></a></td></tr>',
  body: 'tbody',
  holder: '<div class="div-table"><table class="classictable">
	<thead><tr>
<th>%%lang.name%%</th><th>%%lang.value%%</th><th>%%lang.del%%</th>
		</tr></thead>
<tbody></tbody >
</table></div>'
  };

}(jQuery, document, window));