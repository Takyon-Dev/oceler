<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TrialController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* Force ssl when running on non-local server */
if (App::environment() !== 'local') {
    URL::forceScheme('https');
}

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/home', function () {
    return Auth::user()->role_id == 2
        ? Redirect::route('admin_home')
        : Redirect::route('player_home');
})->middleware('auth');

// Player routes
Route::middleware(['auth', 'roles:player'])->group(function () {
    Route::get('player/', [PlayerController::class, 'home'])->name('player_home');
    Route::get('player/ping/solution/{last_sol}/message/{last_msg}', [PlayerController::class, 'ping']);
    Route::get('player/trial', [PlayerController::class, 'playerTrial']);
    Route::get('player/trial/initialize', [PlayerController::class, 'initializeTrial']);
    Route::get('player/trial/queue', [TrialController::class, 'enterQueue']);
    Route::get('player/trial/queue/status', [PlayerController::class, 'queueStatus']);
    Route::get('player/trial/instructions', [PlayerController::class, 'showInstructions']);
    Route::get('player/trial/trial-stopped', [PlayerController::class, 'trialStopped']);
    Route::get('player/trial/instructions/status/{id}', [TrialController::class, 'instructionsStatus']);
    Route::get('player/trial/instructions/status/read/{id}', [TrialController::class, 'markInstructionsAsRead']);
    Route::get('player/trial/not-selected/{id}', [TrialController::class, 'notSelectedForTrial']);
    Route::get('player/trial/end-round', [PlayerController::class, 'endTrialRound']);
    Route::get('player/post-trial-survey', [PlayerController::class, 'showPostTrialSurvey']);
    Route::get('player/trial/new-round', [PlayerController::class, 'startTrialRound']);
    Route::get('player/trial/end', [PlayerController::class, 'endTrial']);
    Route::get('player/trial/stopped', [PlayerController::class, 'trialStopped']);
    Route::get('player/end-task/{reason}', [PlayerController::class, 'endTask']);
    Route::post('/solution', [PlayerController::class, 'postSolution']);
    Route::post('/message', [MessageController::class, 'postMessage']);
    Route::get('/listen/system-message/', [MessageController::class, 'getListenSystemMessage']);
    Route::post('/reply', [ReplyController::class, 'postReply']);
    Route::post('/search', [SearchController::class, 'postSearch'])->name('search.post');
    Route::get('/search/reload', [SearchController::class, 'getSearchReload'])->name('search.reload');
});

// Admin routes
Route::middleware(['auth', 'roles:administrator'])->group(function () {
    Route::get('/admin/players', [AdminController::class, 'showPlayers'])->name('admin_home');
    Route::get('/admin/listen/queue', [AdminController::class, 'getListenQueue']);
    Route::get('/admin/listen/trial', [TrialController::class, 'getListenAllTrialPlayers']);
    Route::get('/admin/listen/trial/{id}', [TrialController::class, 'getListenTrialPlayers']);
    Route::get('/admin/trial', [TrialController::class, 'index']);
    Route::get('/admin/trial/create', [TrialController::class, 'create']);
    Route::post('/admin/trial', [TrialController::class, 'store']);
    Route::get('/admin/trial/{id}', [TrialController::class, 'getTrial']);
    Route::get('/admin/trial/config/{id}', [TrialController::class, 'editTrial']);
    Route::post('/admin/stop-all-trials', [TrialController::class, 'stopAllTrials']);
    Route::post('/admin/trial/stop/{id}', [TrialController::class, 'stopTrial'])->name('trial.stop');
    Route::patch('/admin/trial/{id}', [TrialController::class, 'updateTrial'])->name('trial.update');
    Route::delete('/admin/trial/{id}', [TrialController::class, 'destroy'])->name('trial.delete');
    Route::get('/admin/trial/toggle/{id}', [TrialController::class, 'toggle']);
    Route::get('/admin/trial-log', [AdminController::class, 'showLogs']);
    Route::get('/admin/log/{id}', [AdminController::class, 'readLog']);
    Route::get('/admin/log/download/{id}', [AdminController::class, 'downloadLog']);
    Route::get('/admin/config-files', [AdminController::class, 'showConfigFiles']);
    Route::post('/admin/config-files/upload', [ConfigController::class, 'uploadConfig']);
    Route::get('/admin/config-files/delete/{type}/{id}', [ConfigController::class, 'deleteConfig']);
    Route::get('/admin/config-files/view/{name}', [ConfigController::class, 'viewConfig']);
    Route::get('/admin/data', [AdminController::class, 'getData']);
    Route::get('/admin/mturk-log', [AdminController::class, 'viewMturkLog']);
    Route::get('/admin/log', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});

// Testing routes...
Route::get('/player/timer-test', [PlayerController::class, 'timerTest']);
Route::get('/manage-queue', [TrialController::class, 'manageQueue']);
Route::get('/test-manage-queue', [TrialController::class, 'testQueueManager']);
Route::get('/player/is-trial-stopped', [PlayerController::class, 'isTrialStoppedTest']);
Route::get('/player/message-listen-test', [MessageController::class, 'messageListenTest']);
Route::get('/player/date-debug', [PlayerController::class, 'dateDebug']);
Route::get('/test-mturk', [PlayerController::class, 'testMTurk']);
Route::get('/initial-post-trial-survey', [PlayerController::class, 'testInitialPostTrialSurvey']);
Route::get('/post-trial-survey', [PlayerController::class, 'testPostTrialSurvey']);
Route::post('/player/submit-initial-survey', [PlayerController::class, 'postInitialSurvey']);
Route::get('/player/test-earnings-calc/{userId}/{trialId}/{round}', [PlayerController::class, 'testEarningsCalc']);
Route::get('/test-process-hit', [TrialController::class, 'testHitProcess']);
Route::get('/test-whatevs', [TrialController::class, 'testWhatevs']);

// Search Routes
Route::middleware(['auth'])->group(function () {
    // Search interface
    Route::get('/trials/{trial}/rounds/{round}/search', [SearchController::class, 'index'])
        ->name('search.index');

    // Search operations
    Route::post('/trials/{trial}/rounds/{round}/search', [SearchController::class, 'search'])
        ->name('search.perform');

    // Search suggestions
    Route::get('/trials/{trial}/rounds/{round}/suggestions', [SearchController::class, 'suggestions'])
        ->name('search.suggestions');

    // Search statistics
    Route::get('/trials/{trial}/rounds/{round}/stats', [SearchController::class, 'stats'])
        ->name('search.stats');

    // Search history
    Route::get('/trials/{trial}/rounds/{round}/history', [SearchController::class, 'history'])
        ->name('search.history');

    // Check factoid status
    Route::get('/trials/{trial}/rounds/{round}/factoids/{factoid}/check', [SearchController::class, 'checkFactoid'])
        ->name('search.check-factoid');
}); 