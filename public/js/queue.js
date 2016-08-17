function queue(user_id)
{
  $.ajax({
    type: "GET",
    url: "/player/trial/queue/status",
    success: function(status)
    {
      console.log(status);
      if(status == 0){
        window.location.replace("/player/trial/initialize");
      }

      else if(status >= 1){
        $("#players_needed span").html(status);
        $("#players_needed").show();
      }

    }
  });

  setTimeout(queue, 2000);
}
