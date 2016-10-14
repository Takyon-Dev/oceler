function queue()
{
  $.ajax({
    type: "GET",
    url: "/player/trial/queue/status",
    success: function(status)
    {

      if(status == 0){
        window.location.replace("/player/trial/initialize");
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
