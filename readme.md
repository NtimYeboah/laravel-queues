## Learn how to use message queues in Laravel

This is a simple project to demonstrate how to use message queues in Laravel. In this project, we will queue emails to be sent when a user signs up with our application.

If you do not have a solid grasp of the concept of message queues, I recommend you take a look at this excellent article [here](https://daylerees.com/message-queues/) and come back.

When a user signs up, an event is emitted that pushes a notification message to a queue, then eventually the message is executed to send the mail.

## Table of contents

- [Installation and Setup](#installation-and-setup)
- [Running Application](#running-application)
- [Deep Dive](#deep-dive)
    * [Listening to Registered event](#listening-to-registered-event)
    * [Queuing confirmation email notification](#queuing-confirmation-email-notification)
        + [Queuing notification](#queuing-notification)
        + [The queue connection](#the-queue-connection)
        + [The queue name](#the-queue-name)
        + [Job delay](#job-delay)
- [Factors to consider when implementing queues](#factors-to-consider-when-implementing-queues)
    * [Alert users when job fails](#alert-users-when-job-fails)
    * [Logging](#logging)
        + [Logging job execution](#logging-job-execution)
        + [Logging job failure](#logging-job-failure)
    * [Visualize queue metrics](#visualize-queue-metrics) 


## Installation and Setup

Clone this repository by running

```bash
$ https://github.com/NtimYeboah/laravel-queues-example.git
```

Install the packages by running the composer install command

```bash
$ composer install
```

Set your database credentials in the .env file

Set your redis credentials in the `.env` file

```bash
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

Sign up for [Mailtrap](https://mailtrap.io/) and set your mail credentials in the `.env` file

```bash
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=null
```

The confirmation email will be sent to mailtrap.

Run the migrations

```bash
$ php artisan migrate
```

Run the queue

```bash
$ php artisan queue:work redis --queue=emails:verify-account
```

View queue metrics

We will use Laravel [horizon](https://laravel.com/docs/5.6/horizon) to allow us to monitor key metrics in our queue system. We can start horizon by using the artisan command:

```bash
$ php artisan horizon
```

We can view horizon dashboard by visiting `/horizon` on your host.

## Running Application

Register a new user, a message will be shown telling you to confirm your email.


## Deep Dive

When a user registers Laravel emits the `Illuminate\Auth\Events\Registered` event. This event holds the registered user. This event is listened to and the listener pushes a confirmation email notification to the `emails:verify-account` queue.

### Listening to Registered event

To listen to the `Illuminate\Auth\Events\Registered` event, we register a listener in the `App\Providers\EventServiceProvider`. The name of the listener is `VerifyAccount`

[https://github.com/NtimYeboah/laravel-queues-example/app/Providers/EventServiceProvider.php](https://github.com/NtimYeboah/laravel-queues-example/blob/master/app/Providers/EventServiceProvider.php)

```php
...

/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'Illuminate\Auth\Events\Registered' => [
        'App\Listeners\VerifyAccount',
    ],
];

...
```

The listener class specifies the logic to run to send the notification.

[https://github.com/NtimYeboah/laravel-queues-example/app/Listeners/VerifyAccount.php](https://github.com/NtimYeboah/laravel-queues-example/blob/master/app/Listeners/VerifyAccount.php)

```php
...
/**
 * Handle the event.
 *
 * @param  Registered  $event
 * @return void
 */
public function handle(Registered $event)
{
    $user = $event->user;

    ...

    $user->sendAccountVerificationNotification($verification->token);
}
...
```

### Queuing confirmation email notification

The notification to send the email will be queued so as to improve the response time of our app. Typically, you can manually test a scenario where the notification is queued and a scenario where the notification is not queued. You will notice the response is faster in the scenario where the notification is queued. You can verify this by observing the response time of each request using the network tab of chrome.

To queue a notification, we must specify the connection the notification should be sent to, the name of the queue the notification should be sent to and the time the job should wait before it's executed.

#### Queuing notification

To queue notifications, you have to implement the `Illuminate\Contracts\Queue\ShouldQueue` interface provided by Laravel. 

[https://github.com/NtimYeboah/laravel-queues-example/app/Notifications/VerifyAccountNotification.php](https://github.com/NtimYeboah/laravel-queues-example/blob/master/app/Notifications/VerifyAccountNotification.php)

```php
use Illuminate\Contracts\Queue\ShouldQueue;
...

class VerifyAccountNotification extends Notification implements ShouldQueue
{
...

```


#### The queue connection

We set a property in the notification class called `connection` to the specify the connection the notification should be sent to. In our case, we will use [redis](https://laravel.com/docs/5.6/redis).

```php
...
/**
 * The name of the connection the notification should be sent to.
 * 
 * @var string|null
 */
     
public $connection = 'redis';
...
```

#### The queue name

For the queue name, we also have to set a property in the notification class to specify the queue name. In this case we call the queue name `emails:verify-account`

```php
...
/**
 * The name of the queue the notification should be sent to.
 * 
 * @var string|null
 */
public $queue = 'emails:verify-account';
...
```

#### Job delay

In our case, we don't want the job to delay before it's executed. So we set the `delay` propery to `null`

```php
...
/**
 * The time the job should wait before its executed.
 * 
 * @var DateTime|null
 */
public $delay = null;
...
```

### Factors to consider when implementing queues

Queued jobs are executed outside of the usual request-response lifecycle, and just like any other system, things will not go according to plan. But rest assured, there are measures that can be put in place to make sure that you are in control. This section takes a look at those measures;

#### Alert users when job fails

There are some instance where jobs fails after reaching the maximum retry limit. In such cases, alert the user so the action can be retaken.

In Laravel, you can define a `failed` method that will be called when the job fails. Inside this method, you can alert the user so the action can be retaken.

```php
...

/**
 * The job failed to process.
 *
 * @param  Exception  $exception
 * @return void
 */
public function failed(Exception $exception)
{
    // Send user notification of failure, etc...

}
...
```

#### Logging 

You can log when queue jobs are about to execute, when the job finish running and when the job fails. 

##### Logging job execution

You can log when queue jobs are about to execute, when the job finish running. In this instance, when going through your logs and request are hitting endpoints that queue jobs however, there are no logs of jobs being executed, then you know things are wrong.

Laravel provide events `before` and `after` which are fired when queues are about to be executed and when queues are done executing. You can listen to these events and log the connection, the specific job being executed and the payload or message being queued.

You can listen to these events and log in the `App\Providers\AppServiceProvider.php` class.

[https://github.com/NtimYeboah/laravel-queues-example/app/Providers/AppServiceProvider.php](https://github.com/NtimYeboah/laravel-queues-example/blob/master/app/Providers/AppServiceProvider.php)


```php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

...

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

    ...
}

...

```

##### Logging job failure

You can as well log jobs that fail to execute. You can listen to the `failing` event in the `App\Providers\AppServiceProvider.php` class.

```php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;  

...

public function boot()
{
    Queue::failing(function (JobFailed $job) {
        Log::error('Job failed', [
            'connection' => $event->connectionName,
            'job' => $event->job,
            'exception' => $event->exception
        ]);
    });

    ...
}

...

```


#### Visualize queue metrics

Viewing server logs is a tedious task for most developers. Again its difficult to get key metrics for you queue systems if you rely on only logs. Using a visualizing tools helps you an overview of how your queues are running. 

Laravel provides [horizon](https://laravel.com/docs/5.6/horizon), a beautiful dashboard and code-driven configuration for your queues when using redis as the queue driver.

You can start horizon by using the artisan command;

```bash
$ php artisan horizon
```

We can view horizon dashboard by visiting `/horizon` on your host.