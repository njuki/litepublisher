var msword_spellchecker= {
wordApp : null,
doc: null,

create: function() {
 var WIN_MIN = 1; 
var wordapp = new ActiveXObject("Word.Application"); 
wordapp.Connect;
      wordapp.ScreenUpdating = false; // speed up winword's processing
      wordapp.Visible = false; 
 wordapp.WindowState = WIN_MIN ; 

var l =wordapp.CustomDictionaries.count;
for (var i = 1; i <= l; i++) {
var item = wordapp.CustomDictionaries.Item(i);
wordapp.CustomDictionaries.ActiveCustomDictionary = item;
}

/*
 setTimeout(function () { 
     Word.WindowState = WIN_MAX; 
 }, 500); 
*/

  this.doc =wordapp.Documents.Add();
this.wordapp = wordapp;
},

checkword: function(word) {
var EmptyParam = null;
var lIgnore = true;
this.wordapp.Visible = false; // CheckSpelling makes the document visible 
if (this.wordapp.CheckSpelling(word, EmptyParam, lIgnore)) return true;
var suggestions =  this.wordapp.GetSpellingSuggestions(word);
var c = suggestions.count;
result = [];
for (var i = 1; i <= c; i++) {
result.push(suggestions.Item(i));
}
return result;
},

close: function() {
this.wordapp.Quit(false);
}

};
