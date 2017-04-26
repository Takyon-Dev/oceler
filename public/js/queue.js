function queue()
{
  $.ajax({
    type: "GET",
    url: "/player/trial/queue/status",
    success: function(status)
    {

      if(status == 0){
        window.location.replace("/player/trial/instructions");
      }

      else if(status >= 1){
        $("#players_needed span").html(status);
        $("#players_needed").show();
      }

    }
  });

  setTimeout(leaveQueue, 300000);
  setTimeout(queue, 2000);
}

function waitForInstructions(trial_id)
{

  $.ajax({
    type: "GET",
    url: "/player/trial/instructions/status/" + trial_id,
    success: function(status)
    {

      json = $.parseJSON(status);
      if(json.response){
        window.location.replace("/player/trial/initialize");
      }

      setTimeout(function(){
        waitForInstructions(trial_id);
      }, 1000);
    }
  });

}

function markAsRead(user_id)
{

  $.ajax({
    type: "GET",
    url: "/player/trial/instructions/status/read/" + user_id,
    success: function(status)
    {
      return;
    }
  });

}

function leaveQueue()
{
  window.location.replace("/player/trial/timeout");
}
