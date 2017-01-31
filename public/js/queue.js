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

      else{
        $("#queue_content h1").html('There are no trials available at this time.');
        $("#queue_content img").hide();
      }

    }
  });

  setTimeout(queue, 2000);
}

function waitForInstructions(trial_id)
{

  $.ajax({
    type: "GET",
    url: "/player/trial/instructions/status/" + trial_id,
    success: function(status)
    {

      if(status.response){
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
