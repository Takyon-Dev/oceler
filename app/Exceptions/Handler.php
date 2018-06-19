<?php

namespace oceler\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Auth;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if(Auth::user()){
          Log::info("USER ID ". Auth::user()->id ." has generated an Exception");
        }
        else {
          Log::info("A non-logged-in user has generated an Exception");
        }

        Log::info("Exception ". get_class($e) .' '.$e->getMessage() ." URI: ".$_SERVER['REQUEST_URI']);

        //return parent::render($request, $e);
        //Log::info("USER ID ". $u_id ." is loading main trial page");
        // If we are on the dev server (local) or an admin

        if(app()->isLocal() || (Auth::user() && Auth::user()->role_id == 2)) {
          return parent::render($request, $e);
        }

        // Otherwise, don't render the exception, instead take them to generic error message
        return redirect('/player/trial/trial-stopped');
    }
}
