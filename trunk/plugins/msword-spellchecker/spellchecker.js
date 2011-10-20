fvar area_spellchecker = {
d
uplicate_words: true,
  curpos: 0,
 start: 0,
 laststart: 0,
 word: "",
lastword: "",
 mewword: "",
 StripedWord: wideString;

area: null,

spellcheck: function(area) {
this.area = area;
 this.lastword = "";
  this.laststart = 0;
var sel = area_getsel(area);
  this.curpos = sell.start;
return this.spell_loop("init");
}

spell_loop: function(prevstep) {
  while (this.curpos < this.area.value.length) {
if ("init" == prevstep) {
    this.start = this.find_start_word(this.area, this.curpos);
    this.curpos = this.find_end_word(this.area, this.curpos);
    this.Word = this.area.value.substr(this.start, this.curpos - this.start + 1);
            area_setsel(this.area, this.start, this.start);
}

    if (this.duplicate_words && (this.Word == this.lastword)) {
return this.repeative_word_dlg(this.area.value.substr(this.laststart, this.curpos - this.laststart + 1);
}

    this.lastword = this.Word;
    this.laststart = this.start;
    if not CheckWord(Word, StripedWord) then {
      case MisWordDlg(Word, NewWord) of
        mrCancel: {
            area.SelStart = this.start;
            return false;
          }
        scIgnore:
          area.SelStart = this.start + Length(Word);
        scIgnoreAll: {
            FIgnoreAll.Add(Tnt_WideUppercase(NewWord));
            area.SelStart = this.start + Length(Word);
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
          } }
      this.lastword = '';
sel = area_getsel(this.area);
      this.curpos = sel.start;
    } else this.curpos++;

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
}


repeative_word_dlg: function(word) {
          mrYes: {
              area.SetSelection(this.start, this.curpos + 1, true);
              area.ClearSelection;
              this.curpos = this.start;
              return this.spell_loop("dupl");
            }
          mrCancel: {
              area.SelStart = this.start;
              return false;
            }
        }

    this.lastword = this.Word;
    this.laststart = this.start;
    if not CheckWord(Word, StripedWord) then {

};

$(document).ready(function() {
alert($("#content").val());

try {
msword_spellchecker.create();
alert(msword_spellchecker.checkword('проверкэ'));
 } catch(e) { alert(e.message); }
});