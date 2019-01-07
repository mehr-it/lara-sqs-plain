# lara-sqs-plain
Allows to receive custom/plain SQS messages in Laravel. This is very useful if you have to
work with third party messages not dispatched from Laravel.

You receive the plain message content in the constructor of your handler classes. You may
specify your own handler class for each queue.

This package is based on the [mehr-it/lara-sqs-ext](https://github.com/mehr-it/lara-sqs-ext)
package and implements most of it's features.

## Installation

	composer require mehr-it/lara-sqs-plain
	
This package uses Laravel's package auto-discovery, so the service provider will be loaded 
automatically.

## Queue configuration

Just configure a queue connection as you would configure any other SQS queue in Laravel.
The only difference is the `message_handler` option, which must be the name of the class which should
handle the messages:

	'sqs-plain-conn' => [
		'driver'          => 'sqs-plain',
		'key'             => '112233445566778899',
		'secret'          => 'xxxxxxxxxxxxxxxxxxxxxxxxxx',
		'prefix'          => 'https://sqs.eu-central-1.amazonaws.com/11223344556677',
		'queue'           => 'msgs',
		'region'          => 'eu-central-1',
		'message_handler' => 'App\\Job\\MyPlainHandler',
	],
	
### Long polling

To enable long polling, you may add the option `message_wait_timeout` to the queue
configuration. This sets the `WaitTimeSeconds` parameter to the configured amount of time.

	'message_wait_timeout' => 20,
	
## Message handlers

To create a handler for SQS messages, simply extend the `SqsPlainMessageHandler` class.
The `handle` function will be invoked for each message and the raw message body will
be available in the `message` attribute:

	class MyPlainHandler extends SqsPlainMessageHandler {
		
		public function handle() {
		
			$rawMessage = $this->message;
		}
						
	}
	
The `InteractsWithQueue` and `InteractsWithSqsQueue` trait is already implemented in the base class, so you can interact
with the queue.

Message handlers my define the same properties as common jobs, to control the job
workers. This includes `tries`, `timeout`, `timeoutAt`, `automaticQueueVisibility`,
`automaticQueueVisibilityExtra` or the `retryUntil` method.

## Thanks
Thanks to Jussi Hamalainen who wrote the `jusahah/laravel-sqs-jobless` package which served
as guideline for this package.