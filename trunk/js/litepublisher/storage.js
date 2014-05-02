/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  litepubl.Storage = Class.extend({
    get: false,
    set: false,
    remove: false,
    
    init: function() {
      if ("storage" in litepubl) return;
      var works = false;
      
      if("localStorage" in window){
        try {
          var k = 'litepubl_test';
          var v = 'testvalue';
          window.localStorage.setItem(k, v);
          works = true;
          window.localStorage.removeItem(k);
      } catch(e) {}
        if (works) {
          this.get = $.proxy(window.localStorage.getItem, window.localStorage);
          this.set = $.proxy(window.localStorage.setItem, window.localStorage);
          this.remove = $.proxy(window.localStorage.removeItem, window.localStorage);
          return;
        }
      }
      
      if("globalStorage" in window){
        try {
          if(window.globalStorage) {
            if(window.location.hostname == "localhost"){
              var storage = window.globalStorage["localhost.localdomain"];
            }else{
              var storage = window.globalStorage[window.location.hostname];
            }
            works = true;
          }
      } catch(e) {}
        
        if (works) {
          this.get = $.proxy(storage.getItem, storage);
          this.set = $.proxy(storage.setItem, storage);
          this.remove = $.proxy(storage.removeItem, storage);
          return;
        }
      }
      
      try{
        var link = document.createElement("link");
        if(link.addBehavior){
          //link.style.behavior = "url(#default#userData)";
          link.addBehavior("#default#userData");
          document.getElementsByTagName("head")[0].appendChild(link);
          link.load();
          works = true;
        }
    } catch( e ) {}
      
      if (works) {
        this.get =  function( key ) {
          link.load();
          return link.getAttribute( key );
        };
        
        this.set= function( key, value ) {
          link.setAttribute( key, value );
          link.save();
        };
        
        this.remove = function( key ) {
          link.removeAttribute( key );
          link.save();
        };
        
        return;
      }
      
      //via cookie
      this.get =  function( key ) {
        return $.cookie(key);
      };
      
      this.set= function( key, value ) {
        $.cookie(key, value, {
          path: '/',
          expires: 3650
        });
      };
      
      this.remove = function( key ) {
        $.cookie(key, false);
      };
      
    }
    
  });//storage

litepubl.getstorage = function() {
if ("storage" in litepubl) return litepubl.storage;
return litepubl.storage = new litepubl.Storage();
};
  
}(jQuery, document, window));