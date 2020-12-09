<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:38
	 */

	namespace MehrIt\LaraSqsPlain\Provider;


	use Illuminate\Queue\QueueManager;
	use Illuminate\Support\ServiceProvider;
	use MehrIt\LaraSqsPlain\Queue\Connectors\SqsPlainConnector;
	use MehrIt\LaraSqsPlain\Queue\Jobs\SqsPlainJob;

	class SqsPlainServiceProvider extends ServiceProvider
	{

		public function boot() {
			/** @var QueueManager $manager */
			$manager = $this->app['queue'];

			$manager->addConnector('sqs-plain', function () {
				return new SqsPlainConnector();
			});
		}


		public function register() {

			// register resolver for SqsPlainJob
			app()->bind(SqsPlainJob::class, /** @noinspection PhpUnusedParameterInspection */
			function($app, $params) {

				return new SqsPlainJob(
					$params['container'],
					$params['sqs'],
					$params['job'],
					$params['connectionName'],
					$params['queue'],
					$params['queueOptions']['message_handler']
				);

			});
		}


	}