function htmlentities(val, quote_style) {
  var out = new String(val).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  if (quote_style != 0)
  out = out.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
  return out;
}


function var_export(val, ret, whitespaces) {
  var type = typeof val;
  var indent = '';
  if (whitespaces !== undefined)
  {
    for (var i = 0; i < whitespaces; i++)
    indent += '  ';
  }
  else
  {
    whitespaces = 0;
  }
  switch(type)
  {
    case 'string':
    return '\'' + val.replace(/'/g, '\'\'') + '\'';
    case 'number':
    case 'boolean':
    return val.toString();
    case 'object':
    // we should be able to use val instanceof Null, but FF refuses it...
    // nb: check nulls first, since they have no attributes
    if (val === null)
    {
      return 'null';
    }
    else if (val instanceof Array)
    {
      var arr = '[\n';
      for(var i = 0; i < val.length; ++i)
      {
        arr += indent + '  ' + var_export(val[i], ret, whitespaces+1) + ',\n';
      }
      arr += indent + ']';
      return arr;
    }
    else
    {
      // generic js object. encode all members except functions
      var arr = '{\n';
        for(var attr in val)
        {
          if (typeof val[attr] != 'function')
          {
            arr += indent + '  \'' + attr + '\' => ' + var_export(val[attr], ret, whitespaces+1) + ',\n';
          }
        }
      arr += indent + '}';
      return arr;
    }
    // match 'function', 'undefined', ...
    default:
    return indent + type;
  }
}