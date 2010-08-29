/*****************************************/
// Name: Javascript Textarea HTML Editor
// Author: Balakrishnan
// URL: http://www.corpocrat.com
//
// Modified by sartas
// URL: http://sartas.ru
/******************************************/


var textarea
var content

function init_OnLoad(e)
{
var obj = document.body
 if(obj)
	eval(e)
 else
 {
	if(!_documentReady)
		setTimeout(function(){init_OnLoad(e)}, 10)
	else
	{
		obj = document.body
 
		if(obj)
		eval(e)
	}
 }
}

function edToolbar(obj) 
{
	if (obj == 'all')
	{
		textarea = document.getElementsByTagName("textarea")
 
		for(i=0;i<textarea.length;i++)
		{
			var toolbar = document.createElement('div')
			toolbar.className = 'toolbar'
			toolbar.innerHTML = buttons(textarea[i].id)
			textarea[i].parentNode.insertBefore(toolbar, textarea[i])
		}
	}
	else
	{
		var toolbar = document.createElement('div')
		toolbar.className = 'toolbar'
		toolbar.innerHTML = buttons(obj)
		textarea = document.getElementById(obj)
		textarea.parentNode.insertBefore(toolbar, textarea)
	}
}

function buttons(obj)
{
var buttons = "<a href='javascript:void(0);' onClick=\"doAddTags('<strong>','</strong>','" + obj + "')\">b<span>&lt;strong&gt;*&lt;/strong&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doAddTags('<em>','</em>','" + obj + "')\">i<span>&lt;em&gt;*&lt;/em&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doAddTags('<h4>','</h4>','" + obj + "')\">h4<span>&lt;h4&gt;*&lt;/h4&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doAddTags('<br />','','" + obj + "')\">br<span>&lt;br /&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doAddTags('<pre><code>','</code></pre>','" + obj + "')\">?&gt;<span>&lt;pre&gt;&lt;code&gt;*&lt;/code&gt;&lt;/pre&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doList('<ul>','</ul>','" + obj + "')\">ul<span>&lt;ul&gt;<br />&nbsp;&nbsp;&lt;li&gt;*&lt;\/li&gt;<br />&lt;/ul&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doList('<ol>','</ol>','" + obj + "')\">ol<span>&lt;ol&gt;<br />&nbsp;&nbsp;&lt;li&gt;*&lt;\/li&gt;<br />&lt;/ol&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doImage('" + obj + "')\">img<span>&lt;img&nbsp;src='' /&gt;</span></a>" +
	"<a href='javascript:void(0);' onClick=\"doURL('" + obj + "')\">url<span>&lt;a&nbsp;href=''&gt;*&lt;/a&gt;</span></a>"
	
	return buttons
}
	
function doImage(obj)
{
	textarea = document.getElementById(obj)
	var url = prompt(IMGpromt,'http://')
	var scrollTop = textarea.scrollTop
	var scrollLeft = textarea.scrollLeft
	
	if (url != '' && url != null)
	{
		if (document.selection) 
		{
			textarea.focus()
			var sel = document.selection.createRange()
			sel.text = '<img src=\"' + url + '\" alt=\"\" />'
		}
	   	else 
		{
			var len = textarea.value.length
			var start = textarea.selectionStart
			var end = textarea.selectionEnd
			var sel = textarea.value.substring(start, end)
			var rep = '<img src=\"' + url + '\" alt=\"\" />'
			textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len)
			textarea.scrollTop = scrollTop
			textarea.scrollLeft = scrollLeft
		}
	 }
}

function doURL(obj)
{
	textarea = document.getElementById(obj)
	var url = prompt(URLpromt,'http://')
	var scrollTop = textarea.scrollTop
	var scrollLeft = textarea.scrollLeft
	
	if (url != '' && url != null)
	{
		if (document.selection) 
		{//IE
			textarea.focus()
			var sel = document.selection.createRange()
					
			if(sel.text=="")
			{
				sel.text = '<a href=\"' + url + '\">' + url + '</a>'
			} 
			else 
			{
				sel.text = '<a href=\"' + url + '\">' + sel.text + '</a>'
			}				
		}
		else 
		{//Firefox
			var len = textarea.value.length
			var start = textarea.selectionStart
			var end = textarea.selectionEnd
			var sel = textarea.value.substring(start, end)
			
			if(sel=="")
			{
				var rep = '<a href=\"' + url + '\">' + url + '</a>'
			}
			else
			{
				var rep = '<a href=\"' + url + '\">' + sel.text + '</a>'
			}

			textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len)
			textarea.scrollTop = scrollTop
			textarea.scrollLeft = scrollLeft
		}
	 }
}
	
function doAddTags(tag1,tag2,obj)
{
	textarea = document.getElementById(obj)

	if (document.selection) 
	{//IE
		textarea.focus()
		var sel = document.selection.createRange()
		sel.text = tag1 + sel.text + tag2
	}
	else
	{//Firefox
		var len = textarea.value.length
		var start = textarea.selectionStart
		var end = textarea.selectionEnd
		var scrollTop = textarea.scrollTop
		var scrollLeft = textarea.scrollLeft
		var sel = textarea.value.substring(start, end)
		var rep = tag1 + sel + tag2
		textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len)
		textarea.scrollTop = scrollTop
		textarea.scrollLeft = scrollLeft
	}
}

function doList(tag1,tag2,obj)
{
	textarea = document.getElementById(obj)

	if (document.selection) 
	{//IE
		textarea.focus()
		var sel = document.selection.createRange()
		var list = sel.text.split('\n')
		var lenList = list.length - 1

		for(i=0;i<lenList;i++) 
		{
			list[i] = ' <li>' + list[i].substr(0,list[i].length-1) + '</li>'
		}

		list[lenList] = ' <li>' + list[lenList] + '</li>'
		sel.text = tag1 + '\n' + list.join("\n") + '\n' + tag2
	}
	else
	{//Firefox
		var len = textarea.value.length
		var start = textarea.selectionStart
		var end = textarea.selectionEnd
		var i
		var scrollTop = textarea.scrollTop
		var scrollLeft = textarea.scrollLeft
		var sel = textarea.value.substring(start, end)		
		var list = sel.split('\n')
		var lenList = list.length - 1
	
		for(i=0;i<lenList;i++)
		{
			list[i] = ' <li>' + list[i].substr(0,list[i].length-1) + '</li>'
		}

		list[lenList] = ' <li>' + list[lenList] + '</li>'
		var rep = tag1 + '\n' + list.join("\n") + '\n' +tag2
		textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len)
		textarea.scrollTop = scrollTop
		textarea.scrollLeft = scrollLeft
	}
}