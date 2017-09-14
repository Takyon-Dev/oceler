<div class="col-md-8 col-md-offset-2">
      <form class="survey" action="/player/submit-post-trial-survey" method="POST">
        <label class="statement">
          How much did you enjoy this HIT?
        </label>
        <ul class='likert'>
          <li>
            <input type="radio" name="enjoy" value="1">
            <label>Not much at all</label>
          </li>
          <li>
            <input type="radio" name="enjoy" value="2">
          </li>
          <li>
            <input type="radio" name="enjoy" value="3">
          </li>
          <li>
            <input type="radio" name="enjoy" value="4">
            <label>Very much</label>
          </li>
        </ul>
        <label class="statement">
          How confident are you that you entered the right answers?
        </label>
        <ul class='likert'>
          <li>
            <input type="radio" name="confident" value="1">
            <label>Not confident at all</label>
          </li>
          <li>
            <input type="radio" name="confident" value="2">
          </li>
          <li>
            <input type="radio" name="confident" value="3">
          </li>
          <li>
            <input type="radio" name="confident" value="4">
            <label>Very confident</label>
          </li>
        </ul>
        <div class="form-group">
          <label for="comments">
             If you have any other comments for our team, please let us know here:
          </label>
          <textarea name="comments" class="form-control" rows="3"></textarea>
        </div>
        <input type="hidden" name="trial_id" value="{{ $trial_id }}">
        <div class="text-center">
          {!! Form::submit('Continue', ['class' => 'btn btn-primary btn-lg'] ) !!}
        </div>
      </form>
</div>
