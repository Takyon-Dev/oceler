<div class="col-md-8 col-md-offset-2">
      <form class="survey" action="/player/submit-initial-survey" method="POST">
        {{ csrf_field() }}
        <label class="statement">
          How well did you understand the instructions?
        </label>
        <ul class='likert'>
          <li>
            <input type="radio" name="understand" value="1">
            <label>Not well at all</label>
          </li>
          <li>
            <input type="radio" name="understand" value="2">
          </li>
          <li>
            <input type="radio" name="understand" value="3">
          </li>
          <li>
            <input type="radio" name="understand" value="4">
            <label>Very well</label>
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
          <label for="email">
            To be notified when we post similar HITs, please provide an email address:
          </label>
          <ul>
            <li>future HITs will involve real-time problem solving with other Turkers</li>
            <li>Your email address will only be used to announce HITs and will not be shared with others</li>
          </ul>
          <input type="email" class="form-control" name="email" placeholder="Email">
        </div>
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
