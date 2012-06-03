(function( $ ){
$.pollclient = function() {

  var pollclient = {
enabled: true,
    voted : [],
init: function () {
$(".pollitem").click(function() {
      pollclient.clickvote($(this).data("idpoll"), $(this).data["index"));
      return false;
    });
    
$(".submit-radio-poll").click(function() {
      var vote = $("input:radio:checked", $(this).closest(".activepoll")).val();
      pollclient.clickvote($(this).data("idpoll"), vote);
      return false;
    });
  },
  
  pollclient.clickvote = function(idpoll, vote) {
if ($.inArray(idpoll, pollclient.voted) >= 0) return pollclient.error(lang.poll.voted);
        pollclient.setenabled(false);
    pollclient.voted.push(idpoll);
$.litejson({method: "polls_sendvote", id: idpoll, vote: vote}, function(r) {
if (r.code == "error") return pollclient.error(r.message);
        pollclient.setenabled(true);
//update results
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
      
