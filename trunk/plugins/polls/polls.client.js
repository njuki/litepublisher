(function( $ ){
$.pollclient = function() {

  var pollclient = {
    voted : [],
init: function () {
$(".pollitem").click(function() {
      pollclient.clickvote($(this).data("idpoll"), $(this).data["index"));
      return false;
    });
    
    $("form[id^='pollform_radio_']").submit(function() {
      var vals = $(this).attr('id').split('_');
      var vote = $('input:radio:checked', $(this)).val();
      pollclient.clickvote(vals[2], vote);
      return false;
    });
  },
  
  pollclient.clickvote = function(idpoll, vote) {
        pollclient.setenabled(false);
if ($.inArray(idpoll, pollclient.voted) >= 0) return pollclient.error(lang.poll.voted);
    pollclient.voted.push(idpoll);
$.litejson({method: "polls_sendvote", id: idpoll, vote: vote}, function(r) {
if (r.code == "error") return pollclient.error(r.message);
        pollclient.setenabled(true);

      }
    })
    .fail( function(jq, textStatus, errorThrown) {
      //alert('error ' + jq.responseText );
      pollclient.error();
    });
  },
  
      error: function(mesg) {
        pollclient.setenabled(true);
        $.messagebox(lang.dialog.error, mesg);
      },

      setenabled: function(value) {
        if (value== this.enabled) return;
        this.enabled = value;
        if(value) {
                $(":input", ".activepoll").attr("disabled", "disabled");
        } else {
                    $(":input", ".activepoll").removeAttr("disabled");
        }
      },
      
