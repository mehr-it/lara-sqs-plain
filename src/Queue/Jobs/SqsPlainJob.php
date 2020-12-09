<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:50
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Jobs;


	use Aws\Sqs\SqsClient;
	use DateTimeInterface;
	use Illuminate\Container\Container;
	use MehrIt\LaraSqsExt\Queue\Jobs\SqsExtJob;

	class SqsPlainJob extends SqsExtJob
	{
		protected $messageHandler;

		protected $fakedBody;

		/**
		 * Create a new job instance.
		 *
		 * @param  \Illuminate\Container\Container $container
		 * @param  \Aws\Sqs\SqsClient $sqs
		 * @param  array $job
		 * @param  string $connectionName
		 * @param  string $queue
		 * @param string $messageHandler
		 */
		public function __construct(Container $container, SqsClient $sqs, array $job, $connectionName, $queue, $messageHandler) {
			parent::__construct($container, $sqs, $job, $connectionName, $queue);

			$this->messageHandler = $messageHandler;
		}

		/**
		 * Gets the message handler
		 * @return string
		 */
		public function getMessageHandler() {
			return $this->messageHandler;
		}




		/**
		 * Get the raw body string for the job.
		 *
		 * @return string
		 */
		public function getRawBody() {

			if (!$this->fakedBody) {

				$data       = $this->job['Body'];
				$jobHandler = $this->messageHandler;

				// create a job instance
				$job = app()->make($jobHandler, [
					'message' => $data,
				]);

				// we convert the plain body to laravel's native body, calling the given handler
				$this->fakedBody = json_encode([
					'job'                           => 'Illuminate\\Queue\\CallQueuedHandler@call',
					'maxTries'                      => $job->tries ?? null,
					'timeout'                       => $job->timeout ?? null,
					'timeoutAt'                     => $this->getJobExpiration($job),
					'automaticQueueVisibility'      => $job->automaticQueueVisibility ?? true,
					'automaticQueueVisibilityExtra' => $job->automaticQueueVisibilityExtra ?? 0,
					'data'                          => [
						'commandName' => get_class($job),
						'command'     => serialize($job)
					]
				]);
			}

			return $this->fakedBody;
		}

		/**
		 * Get the expiration timestamp for an object-based queue handler.
		 *
		 * @param  mixed $job
		 * @return mixed
		 */
		protected function getJobExpiration($job) {
			if (!method_exists($job, 'retryUntil') && !isset($job->timeoutAt))
				return null;

			$expiration = $job->timeoutAt ?? $job->retryUntil();

			return $expiration instanceof DateTimeInterface
				? $expiration->getTimestamp() : $expiration;
		}
	}