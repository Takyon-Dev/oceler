<form action="{{$submit_to}}/mturk/externalSubmit" method="POST">
  <input type="hidden" name="assignmentId" id="assignmentId" value="{{$assignment_id}}">
  <input type="hidden" name="passed_trial" value="{{ $passed_trial }}">
  <input type="hidden" name="bonus" value="{{ $total_earnings['bonus'] }}">
  <input type="hidden" name="completed_trial" value="{{ $completed_trial }}">
  <input class="btn btn-primary btn-lg" type="submit" value="Submit">
</form>
