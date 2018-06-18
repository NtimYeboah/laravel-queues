## Learn how to use message queues in Laravel

This is a simple project to demonstrate how to use message queues in Laravel. In this project, we will queue emails to be sent when a user signs up with our application.

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

When a user registers Laravel emits the `'Illuminate\Auth\Events\Registered` event. This event holds the registered user. This event is listened to and the listener pushes a confirmation email notification to the `emails:verify-account` queue.

### Listening to Registered event

To listen to the `'Illuminate\Auth\Events\Registered` event, we register a listener in the `App\Providers\EventServiceProvider`. The name of the listener is `VerifyAccount`

[](https://github.com/NtimYeboah/laravel-queue-example/app/Providers/EventServiceProvider.php)

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

The listener class specifies the logic to run to send the notification. In the listener.

[](https://github.com/NtimYeboah/laravel-queue-example/app/Listeners/VerifyAccount.php)

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

The notification to send the email will be queued so as to improve the response time of our app. Typically, you can manually test a scenario where the notification is queued and a scenario where the notification is not queued. You will notice that the response is faster in former scenario. You can verify this by observing the response time of each request using the network tab of chrome.

To queue a notification, we must specify the connection the notification should be sent to, the name of the queue the notification should be sent to and the time the job should wait before it's executed.

#### Queuing notification

To queue notifications, Laravel provides the `Illuminate\Contracts\Queue\ShouldQueue` interface that the notification class has to implement.

[](https://github.com/NtimYeboah/laravel-queue-example/app/Notifications/VerifyAccountNotification.php)

```php
use Illuminate\Contracts\Queue\ShouldQueue;
...

class VerifyAccountNotification extends Notification implements ShouldQueue
{
...

```


#### The queue connection

We set a property in the notification class called `connection` to the specify the connection the notification should be sent to. In our case, we will use [redis](https://laravel.com/5.6/redis).

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
