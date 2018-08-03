function queue()
{
  $.ajaxSetup({ cache: false });
  $.ajax({
    type: "GET",
    url: "/player/trial/queue/status",
    success: function(status)
    {

      if(status == 0){
        window.location.replace("/player/trial/instructions");
      }
    }
  });

  // If they are still waiting after 5 mins, leave
  setTimeout(leaveQueue, 300000);
  setTimeout(queue, 2000);
}

function waitForInstructions(trial_id)
{
  $.ajaxSetup({ cache: false });
  $.ajax({
    type: "GET",
    url: "/player/trial/instructions/status/" + trial_id,
    success: function(response)
    {

      //response = $.parseJSON(status);
      if(response.status == 'ready'){
        document.cookie = 'generic_timer=; Max-Age=-99999999;';
        window.location.replace("/player/trial/initialize");
      }

      else if(response.status == 'remove') {
        window.location.replace("/player/trial/not-selected/" + trial_id);
      }
      else if (response.status == 'waiting') {
        console.log(response);
      }

      else {
        console.log('other');
        console.log(response);
      }

      setTimeout(function(){
        waitForInstructions(trial_id);
      }, 1000);
    }
  });

  //setTimeout(leaveQueue, 120000);
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
  window.location.replace("/player/end-task/timeout");
}
