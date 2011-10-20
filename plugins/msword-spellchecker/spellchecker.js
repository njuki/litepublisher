fvar area_spellchecker = {
duplicate_words: true,
ignorelist: [],
customdict: [],
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
var checked = this.checkword(striped);
    if (checked !== true) {
return this.mis_word_dlg(striped, checked);
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

  function StripComments(const Str: widestring; StartChar, FinishChar: widechar): wideString;
  var
    st, en: integer;
  {
    Result = Str;
    St = WideCharPos(StartChar, Result);
    while St > 0 do {
      en = WideCharPos(FinishChar, Result);
      if st > en then // If no closing brack then exit //
        Exit;
      Delete(Result, En, 1);
      delete(Result, St, 1);
      St = WideCharPos(StartChar, Result);
    }
  }

var
  TmpKey, Tmp: wideString;

  Result = Key;
  if fIgnoreBrackets then {
    Result = StripComments(Result, '{', '}');
    Result = StripComments(Result, '(', ')');
    Result = StripComments(Result, '[', ']');
  }
  Result = left_trim(right_trim(Result));

    if fIgnoreInternet and (Result <> '') then {
    if (pos('@', Result) > 0) and (pos('.', Result) > 0) then
      result = '';
    Tmp = Tnt_WideLowerCase(Result);
    if (pos('mailto:', Tmp) > 0) or
      (pos('www.', Tmp) > 0) or
      (pos('http://', Tmp) > 0) or
      (pos('ftp.', Tmp) > 0) or
      (pos('https://', Tmp) > 0) then
      result = '';
  }

    if fIgnoreAllUpperCase and (Result <> '') and (Result = Tnt_WideUppercase(Result)) then {
    result = '';
  }
  if fIgnoreFiles and (Result <> '') then {
    if (Copy(Result, 1, 2) = '\\') or (copy(Result, 2, 2) = ':\') then
      result = '';
  }

  if fIgnoreNumbers then {
    TmpKey = StripDelimiter('0123456789-.' + DateSeparator, Key);
    Tmp = Tnt_WideLowerCase(TmpKey);
    if (TmpKey = '') or
      (((Tmp = 'st') or
      (Tmp = 'nd') or
      (Tmp = 'rd') or
      (Tmp = 'th'))
      and (key <> TmpKey)) then
      result = '';
    Tmp = Tnt_WideUpperCase(TmpKey);
    if (Tmp = 'II') or (Tmp = 'III') or (Tmp = 'IV') or
      (Tmp = 'V') or (Tmp = 'VI') or (Tmp = 'VII') or
      (Tmp = 'VIII') or (Tmp = 'IX') or (Tmp = 'X') or
      (Tmp = 'XI') then
      result = '';
  }
//  if result <> '' then  Result = StripDelimiter('.', result);
}

},

repeative_word_dlg: function(word) {
          mrYes: {
              area.SetSelection(this.start, this.curpos + 1, true);
              area.ClearSelection;
              this.curpos = this.start;
              return this.next_step("dupl");
            }
          mrCancel: {
              area.SelStart = this.start;
              return false;
            }
        }

mis_word_dlg: function(Word, suggest) {
var self = this;
var combo = "";
for (var i = 0, l= suggest.length; i < l; i++) {
combo += "<option>" + suggest[i] + "</option>";
}


        mrCancel: {
            area_setsel(self.area, self.start, self.start);
          }

        scIgnore:
var start = self.start + word.length;
          area_setsel(self.area, start, start);
self.next_step("checked");

        scIgnoreAll: {
            self.ignorelist.push(NewWord);
var start = self.start + word.length;
          area_setsel(self.area, start, start);
          }

        scChange: {
            area.SetSelPos(this.start + pos(StripedWord, Tnt_WideUppercase(Word)) - 1, length(StripedWord));
            area.SelText = NewWord;
            area.SelStart = this.start + length(Word) - length(StripedWord) + length(NewWord);
          }

        scChangeAll: {
            ReplaceAll(area, StripedWord, NewWord);
            area.SelStart = this.start + length(Word) - length(StripedWord) + length(NewWord);
          }

        scAdd: {
            CustomDictAdd(StripedWord);
            area.SelStart = this.curpos + 1;
}
}

checkword: function(word) {
if ((word == '') || ($.inArray(word, this.ignorelist) != -1) || ($.inArray(word, this.customdict) != -1)) return true;
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