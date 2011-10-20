function area_getsel(area) {
            if ('selectionStart' in area) {
var start = area.selectionStart,
end = area.selectionEnd;

return {
start: start,
end : end,
text: area.value.substr(start, end - start)
};
}

area.focus();
if (document.selection) {
var r = document.selection.createRange();
                    if (r != null) {
                    var re = area.createTextRange();
                    var rc = re.duplicate();
                    re.moveToBookmark(r.getBookmark());
                    rc.setEndPoint('EndToStart', re);

                    var rcLen = rc.text.length,
                        i,
                        rcLenOut = rcLen;
                    for (i = 0; i < rcLen; i++) {
                        if (rc.text.charCodeAt(i) == 13) rcLenOut--;
                    }
                    var rLen = r.text.length,
                        rLenOut = rLen;
                    for (i = 0; i < rLen; i++) {
                        if (r.text.charCodeAt(i) == 13) rLenOut--;
                    }
                    
return {
                        start: rcLenOut,
                        end: rcLenOut + rLenOut,
                        text: r.text
};
}
}

return {start: 0, end : 0, text: ""};
}

function area_setsel(area, start, end) {
area.focus();
var l = area.value.length;
					if (typeof start != "number") start = -1;
					if (typeof end != "number") end = -1;
					if (start < 0) start = 0;
					if (end > l) end = l;
					if (end < start) end = start;
					if (start > end) start = end;

            if ('selectionStart' in area) {
						area.selectionStart = start;
						area.selectionEnd = end;
					} else if (document.selection) {
						var range = area.createTextRange();
						range.collapse(true);
						range.moveStart("character", start);
						range.moveEnd("character", end - start);
						range.select();
					}
}

function area_settext(area, text) {
            if ('selectionStart' in area) {
                    area.value = area.value.substr(0, area.selectionStart) + text + area.value.substr(area.selectionEnd, area.value.length);
} else if (document.selection) {
                    area.focus();
                    document.selection.createRange().text = text;
}
}

function area_clear(area) {
area_settext(area, "");
}
