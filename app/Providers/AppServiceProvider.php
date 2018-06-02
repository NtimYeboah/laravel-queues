<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::before(function (JobProcessing $event) {
            Log::info('Starting to process job', [
                'connection' => $event->connectionName,
                'job' => $event->job,
                'payload' => $event->job->payload()
            ]);
       });

       Queue::after(function (JobProcessed $event) {
             Log::info('Finished processing job', [
                'connection' => $event->connectionName,
                'job' => $event->job,
                'payload' => $event->job->payload()
            ]);
       });

       Queue::failing(function (JobFailed $job) {
            Log::info('Job failed', [
                'connection' => $event->connectionName,
                'job' => $event->job,
                'exception' => $event->exception
            ]);
       });
    }
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}