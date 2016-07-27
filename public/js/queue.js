function queue(user_id)
{
  $.ajax({
    type: "GET",
    url: "/player/trial/queue/status",
    success: function(status)
    {

      if(status == 1){
        window.location.replace("/player/trial/initialize");
      }

    }
  });

  setTimeout(queue, 2000);
}
