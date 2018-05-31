# Laravel Queues Example

A simple project to demonstrate how to work with queues in laravel

## Quickstart

Clone this repo `git clone https://github.com/NtimYeboah/laravel-queues-example.git`

## Install Dependencies

`composer install`


## Notification

App\Notifications\VerifyAccountNotification.php

```
class VerifyAccountNotification extends Notification implements ShouldQueue
{
    /**
     * The name of the connection the notification should be sent to.
     * 
     * @var string|null
     */
     
    public $connection = 'redis';
    /**
     * The name of the queue the notification should be sent to.
     * 
     * @var string|null
     */
     
    public $queue = 'emails:verify-account';
    /**
     * The time the job should wait before its executed.
     * 
     * @var DateTime|null
     */
    public $delay = null;
    
    . . .
    
```

## Logging

```
. . .

public function boot()
{
    Queue::before(function (JobProcessing $event) {
         Log::info('Starting to process job', [
              'connection' => $event->connectionName,
              'job' => $event->job,
              'payload' => $event->job->payload()
         ])
    });
    Queue::after(function (JobProcessed $event) {
          Log::info('Finished processing job', [
              'connection' => $event->connectionName,
              'job' => $event->job,
              'payload' => $event->job->payload()
         ])
    });
}

. . . 

```
