(function( window, undefined ) {
  var pollclient = {
    voted : []
  };
  
  window.pollclient = pollclient;
  
  pollclient.init = function () {
    $("*[id^='poll_']").click(function() {
      var vals = $(this).attr("id").split("_");
      pollclient.clickvote(vals[1], vals[2]);
      return false;
    });
    
    $("form[id^='pollform_radio_']").submit(function() {
      var vals = $(this).attr('id').split('_');
      var vote = $('input:radio:checked', $(this)).val();
      pollclient.clickvote(vals[2], vote);
      return false;
    });
  }
  
  pollclient.sendvote = function (idpoll, vote) {
    $.get(ltoptions.url + '/ajaxpollserver.htm',
  {action: 'sendvote', cookie: this.cookie,idpoll: idpoll, vote: vote},
    function (result) {
      var items = result.split(',');
      var idspan = '#pollresult_' + idpoll + '_';
      for (var i =0, n =items.length; i < n; i++) {
        $(idspan + i).html(items[i]);
      }
    })
    .error( function(jq, textStatus, errorThrown) {
      //alert('error ' + jq.responseText );
      pollclient.error_dialog();
    });
  };
  
  pollclient.clickvote = function(idpoll, vote) {
    for (var i = this.voted.length -1; i >= 0; i--) {
      if (idpoll == this.voted[i]) {
        this.error_dialog();
        return false;
      }
    }
    this.voted.push(idpoll);
    
    if (this.cookierequested) {
      this.sendvote(idpoll, vote);
    } else {
      this.cookie = get_cookie("polluser");
      if (this.cookie == null) this.cookie = '';
      this.getcookie(function() {
        pollclient.sendvote(idpoll, vote);
      });
    }
  };
  
  pollclient.getcookie = function(callback) {
    $.get(ltoptions.url + '/ajaxpollserver.htm',
  {action: 'getcookie', cookie: this.cookie},
    function (cookie) {
      if (cookie != pollclient.cookie) {
        set_cookie('polluser', cookie, false);
        pollclient.cookie = cookie;
      }
      
      pollclient.cookierequested = true;
      if ($.isFunction(callback)) callback();
    });
  };
  
  pollclient.error_dialog = function() {
    $.load_ui(function() {
      $(".poll_error_dialog:first").dialog( {
        autoOpen: true,
        modal: true,
        buttons: [
        {
          text: "Ok",
          click: function() {
            $(this).dialog("close");
          }
        } ]
      } );
    });
  };
})(window);