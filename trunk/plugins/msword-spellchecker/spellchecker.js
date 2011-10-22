var lang = {
spellchecker: {
yes: "Yes",
no: "No",
cancel: "Cancel",
ignore: "Ignore",
ignoreall: "Ignore all",
add: "Add"
}
};

fvar area_spellchecker = {
//options
duplicate_words: true,
ignore_brackets: true,
ignore_internet: true,
ignore_files: true,
ignore_numbers: true,
//selectors 
dlg_duplicate: "#dlg_duplicate",
dlg_duplicate_word: "#dlg_duplicate_word",
dlg_checkword: "#dlg_checkword",

//dictionaries
ignoredict: [],
customdict: [],
//private props
  curpos: 0,
 start: 0,
 laststart: 0,
 word: "",
lastword: "",
 mewword: "",
 //StripedWord: wideString;
area: null,

spellcheck: function(area) {
this.area = area;
 this.lastword = "";
  this.laststart = 0;
var sel = area_getsel(area);
  this.curpos = sell.start;
return this.next_step("start");
}

next_step: function(step) {
  while (this.curpos < this.area.value.length) {
switch (step) {
case "start":
    this.start = this.find_start_word(this.area, this.curpos);
    this.curpos = this.find_end_word(this.area, this.curpos);
    this.Word = this.area.value.substr(this.start, this.curpos - this.start + 1);
            area_setsel(this.area, this.start, this.start);

    if (this.duplicate_words && (this.Word == this.lastword)) {
return this.repeative_word_dlg(this.area.value.substr(this.laststart, this.curpos - this.laststart + 1);
}
//! no break here!!

case "dupl":
case "start":
    this.lastword = this.Word;
    this.laststart = this.start;
var striped = this.stripword(this.word);
var suggest = this.checkword(striped);
    if (suggest !== true) {
return this.mis_word_dlg(striped, suggest);
    } else this.curpos++;

case "checked":
      this.lastword = '';
var sel = area_getsel(this.area);
      this.curpos = sel.start;

case "spaces":
case "checked":
case "dupl":
case "start":
    if fDuplicateSpaces then
      if DuplSpaces(area, this.curpos) then {
        area.SelStart = this.curpos;
        case RemoveDupSpaceDlg(area.Lines[area.CaretPos.Y]) of
          mrYes: {
              this.curpos++;
              area.SetSelection(this.curpos, SkipSpaces(area, this.curpos), true);
              area.SelText = '';
            }
          mrCancel: {
              area.SelStart = this.start;
              return false;
            }
        }
      }
    this.curpos = FindNextWord(area, this.curpos);
  }
  return true;
},

find_start_word: function(area, frompos) {
var
  tmp= "",
  i= 0,
j = 0,
  Result = frompos;

  while (Result > 0) {
j = Math.max(0, Result - 32);
    tmp = area.value.substr(j, result - j + 1);
    for (i = tmp.length - 1;  i >= 0; i--) {
      if (tmp.charCodeAt(i) > 32) Result--
      else return result;
}
  }
return result;
}

find_end_word: function(area, frompos) {
var
  tmp= "",
  i, l, Len= 0,

  Result = frompos;
  var Len = area.value.length;
  while (Result < Len) {
    tmp = area.substr(Result, 32);
    l = tmp.length;
    for (i = 0; i < L; i++) 
      if (tmp.charCodeAt(i) > 32) Result++
      else return result;
  }
return result;
},

stripword: function(word) {
var delim = ':;()-_=+=\|/.,~`[]{}!?''"<>';

  function left_trim(s) {
for (var i = 0, l = s.length; i < l; i++) {
if (!((s.charCodeAt(i) <= 32)  || (delim.indexOf(s.charAt(i)) == -1))) break;
}
return s.substr(I);
  }

  function right_trim(s) {
for (var i = s.length -1; i >= 0; i--) {
if (!((s.charCodeAt(i) <= 32)  || (delim.indexOf(s.charAt(i)) == -1))) break;
}
return s.substr(1, I);
  }

  function strip_comments(s, open, close) {
var i = s.indexOf(open);
while (var i >= 0) {
var j = s.indexOf(close);
if (i > j) return s;
s = s.substring(0, i ) + s.substring(i + 1, j) + s.substring(j + 1);
i = s.indexOf(open);
    }
return s;
  }

  var s = $.trim(word);
  if (this.ignore_brackets {
    s = strip_comments(s, '{', '}');
    s = strip_comments(s, '(', ')');
    s = strip_comments(s, '[', ']');
  }

  s = left_trim(right_trim(s));

    if (this.ignore_internet && (s != '')) {
    if ((s.indexOf('@')  >= 0)  && (s.indexOf('.') >= 0)) {
s = "";
} else {
    if ((s.indexOf('mailto:') >= 0) ||
      (s.indexOf('www.') >= 0) ||
      (s.indexOf('http://') >= 0) ||
      (s.indexOf('ftp.') >= 0) ||
      (s.indexOf('https://') >= 0)) s = '';
  }
}

  if (this.ignore_files && (s != '')) {
if (s.substr(0, 2) == '\:') s = "";
  }

  if (this.ignore_numbers && (s != "")) {
if (/^[\d\.\-\,]*?(|st|nd|rd)$/i.test(s)) {
s = "";
} else if (/^(II|III|IV|V|VI|VII|VIII|IX|X|XI)$/i.test(s)) {
      s = "";
  }
}

return s;
},

repeative_word_dlg: function(word) {
var self = this;
$(this.dlg_duplicate_word).text(word);
$(this.dlg_duplicate).dialog( {
autoOpen: true,
modal: true,
buttons: [
{
        text: lang.spellchecker.yes,
        click: function() {
 $(this).dialog("close"); 
              area_setpos(self.area, self.start, self.curpos + 1);
area_clear(self.area);
              self.curpos = self.start;
              return self.next_step("dupl");
            }
    },
{
        text: lang.spellchecker.no,
        click: function() { $(this).dialog("close"); }
 $(this).dialog("close"); 
              area_setsel(self.area, self.start, self.start);
            }
]
} );
        }

mis_word_dlg: function(Word, suggest) {
var self = this;
var combo = "";
var l= suggest.length;
if (l >= 1) {
combo += '<option selected="selected">' + suggest[0] + '</option>';
for (var i = 1; i < l; i++) {
combo += "<option>" + suggest[i] + "</option>";
}
}

var self = this;
$(this.dlg_duplicate_word).text(word);

$(this.dlg_checkword).dialog( {
autoOpen: true,
modal: true,
buttons: [
{
        text: lang.spellchecker.ignore,
        click: function() {
 $(this).dialog("close"); 
var start = self.start + word.length;
          area_setsel(self.area, start, start);
self.next_step("checked");
}
},
{
        text: lang.spellchecker.ignoreall,
        click: function() {
 $(this).dialog("close"); 
            self.ignoredict.push(NewWord);
var start = self.start + word.length;
          area_setsel(self.area, start, start);
self.next_step("checked");
          }
},

{
        text: lang.spellchecker.change,
        click: function() {
 $(this).dialog("close"); 
            area.SetSelPos(self.start + pos(StripedWord, Tnt_WideUppercase(Word)) - 1, length(StripedWord));
            area.SelText = NewWord;
            area.SelStart = this.start + length(Word) - length(StripedWord) + length(NewWord);
self.next_step("checked");
          }
},

{
        text: lang.spellchecker.changeall,
        click: function() {
 $(this).dialog("close"); 
            ReplaceAll(area, StripedWord, NewWord);
            area.SelStart = this.start + length(Word) - length(StripedWord) + length(NewWord);
self.next_step("checked");
          }
},

{
        text: lang.spellchecker.add,
        click: function() {
 $(this).dialog("close"); 
            CustomDictAdd(StripedWord);
            area.SelStart = this.curpos + 1;
self.next_step("checked");
}
},

{
        text: lang.spellchecker.Cancel,
        click: function() {
 $(this).dialog("close"); 
            area_setsel(self.area, self.start, self.start);
          }
}
]);
}

checkword: function(word) {
if ((word == '') || ($.inArray(word, this.ignoredict) != -1) || ($.inArray(word, this.customdict) != -1)) return true;
return msword_spellchecker.checkword(word);
}

};//class

////
$(document).ready(function() {
alert($("#content").val());

try {
msword_spellchecker.create();
alert(msword_spellchecker.checkword('проверкэ'));
 } catch(e) { alert(e.message); }
});