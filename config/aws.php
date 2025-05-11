<?php

use Aws\Laravel\AwsServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in this file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are created. The minimum
    | required options are declared here, but the full set of possible options
    | are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */

    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],
    'gabe_credentials' => [
        'key'    => env('GABE_AWS_ACCESS_KEY_ID'),
        'secret' => env('GABE_AWS_SECRET_ACCESS_KEY'),
    ],
    'region' => env('AWS_REGION', 'us-east-1'),
    'version' => 'latest',
    'endpoint' => env('AWS_ENDPOINT', 'https://mturk-requester-sandbox.us-east-1.amazonaws.com'),
    'http' => [
        'verify' => false
    ],
    'ua_append' => [
        'L5MOD/' . AwsServiceProvider::VERSION,
    ],
    'qualification_ids' => [
        'default' => env('AWS_QUALIFICATION_ID'),
        'gabe' => env('GABE_AWS_QUALIFICATION_ID'),
        'sandbox' => env('SANDBOX_AWS_QUALIFICATION_ID'),
    ],
    'trials' => [
        'within_days' => env('TRIALS_WITHIN_DAYS', 2),
        'no_available_compensation' => env('NO_AVAILABLE_TRIAL_COMPENSATION'),
    ],
    'python' => [
        'path' => env('PATH_TO_PYTHON', '/usr/bin/python'),
        'scripts_path' => env('PATH_TO_PYSCRIPTS', '/var/www/laravel/public/'),
        'cron_log' => env('CRON_OUTPUT_LOG', '/var/www/laravel/storage/logs/cron.log'),
    ]

];
