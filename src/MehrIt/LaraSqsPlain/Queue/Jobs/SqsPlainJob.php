<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:50
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Jobs;


	use Aws\Sqs\SqsClient;
	use Illuminate\Container\Container;
	use Illuminate\Queue\Jobs\SqsJob;
	use Illuminate\Contracts\Queue\Job as JobContract;

	class SqsPlainJob extends SqsJob implements JobContract
	{
		protected $messageHandler;

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

			$data            = $this->job['Body'];
			$jobHandler      = $this->messageHandler;

			// we convert the plain body to laravel's native body, calling the given handler
			$retBody = json_encode([
				"job"  => "Illuminate\Queue\CallQueuedHandler@call",
				"data" => [
					"commandName" => $jobHandler,
					"command"     => serialize(app()->make($jobHandler, [
						'message' => $data,
					]))
				]
			]);

			return $retBody;
		}
	}