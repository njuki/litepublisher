(function( $ ){
  $.fn.litebasket = function(options) {
if (this.basket != undefined) return this;
this.basket = {};
		this.basket.options =  $.extend({
load_ui: false,
sel: {
uidialog: "#basket_dialog",
count: "#basket_count",
price: "#basket_price",
},

footer: {count: "#basket_count",
price: "#basket_count"
},

row: {
name: ".basket_item_name",
count: ".basket_item_name",
price: ".basket_item_price"
},

texts: {
buy: "Buy",
clear: "Clear",
close: "Close"
},
buy_url: "buy.htm"
}, options);  

create_basket(this.basket);
this.basket.create_data();
this.basket.load();
this.basket.refresh();
return this;
};

function create_basket(basket, options) {

basket.create_data = function() {
this.data = {
items: [],
price: 0
}
};

basket.clear = function() {
this.create_data();
this.save();
};

basket.toString = function() {
return $.toJSON (this.data);
};

basket.fromString = function(s) {
if (s && (s != "")) {
//alert(s);
this.data = $.parseJSON(s);
}
};

basket.load = function() {
this.fromString(get_cookie("basket_data"));
};

basket.save = function() {
set_cookie("basket_data", this.toString());
};

basket.getcount= function() {
var result = 0;
for (var i = this.data.items.length - 1; i >= 0; i--) {
result += this.data.items[i].count;
}
return result;
};

basket.getcount= function() {
var result = 0;
for (var i = this.data.items.length - 1; i >= 0; i--) {
result += this.data.items[i].count * this.data.items[i].price;
}
return result;
};

basket.inArray= function(id) {
for (var i = this.data.items.length - 1; i >= 0; i--) {
if (id == this.data.items[i].id) return i;
}
return -1;
};

basket.add = function(id, name, price) {
this.data.price += price;
var index = this.inArray(id);
if (index >= 0) {
this.data.items[index].count++;
} else {
index = this.data.items.push({
id: id,
count: 1,
price: price
});
}
return index;
};

basket.remove = function(id) {
var index = this.inArray(id);
if (index >= 0) {
this.data.price -= this.data.items[index].price * this.data.items[index].count;
this.data.items.splice(index, 1);
}
};

basket.refresh = function() {
};

basket.buy = function() {
window.location = this.options.buy_url;
};

basket.clear_table = function() {
this.clear();
var first =$(row.row + ":first");
$(this.options.sel.basket_item + ":not(:first)", parent).remove(); 
first.hide();
},

basket.showdialog = function() {
var count = 0;
var price = 0;
var row = this.options.itemtable;
var first =$(this.options.basket_item + ":first");
var parent = first.parent();

$(this.options.basket_item + ":not(:first)", parent).remove(); 
var l = this.data.items.length;
if (l == 0) {
first.hide();
} else {
first.show();
for (var i = 0; i < l; i++) {
var item = this.data.items[i];
count += item.count;
price += item.price * item.count;
var elem = i == 0 ? first : first.clone().appendTo(parent);
$(row.count, elem).text(item.count);
$(row.price, elem).text(item.price);
$(row.name, elem).html(this.products.get(item.id));
}
}

$(this.options.footer.count).text(count);
$(this.options.footer.price).text(price);
var basket = this;

basket.load_ui(function() {
$(basket.options.sel.uidialog).dialog({
    autoOpen: true,
    modal: true,
    buttons: [
    {
      text: basket.options.texts.buy,
      click: function() {
        $(this).dialog("close");
basket.buy();
      }
    },

    {
      text: basket.options.texts.clear,
      click: function() {
basket.cllear_table();
}
      },

    {
      text: basket.options.texts.close,
      click: function() {
        $(this).dialog("close");
      }
    } 
]
});
});
};

basket.load_ui= function(fn) {
if ($.isFunction(this.options.load_ui)) {
this.options.load_ui(fn);
} else {
fn();
}
};

};
})( jQuery );