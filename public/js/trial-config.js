/**
 * Displays a config box for n = total # of rounds
 * and groups, as entered in the trial configuration.
 */
function showConfigBoxes(type, n)
{
  // First, remove any cloned elements that might already be there
  $("#" + type + "s" + " ." + type + "-container").not(":eq(0)").remove();

  // Then, for n clone the type of container we need and append it
  // to the appropriate div
  for(i = 2; i <= n; i++){
    var clone = $("." + type + "-container:first").clone();
    $(clone).find("h3 span").html(i);
    $("#" + type + "s").append(clone);
  }
}
