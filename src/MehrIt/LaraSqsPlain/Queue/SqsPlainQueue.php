<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:46
	 */

	namespace MehrIt\LaraSqsPlain\Queue;


	use Aws\Sqs\SqsClient;
	use Illuminate\Queue\SqsQueue;
	use MehrIt\LaraSqsPlain\Queue\Jobs\SqsPlainJob;

	class SqsPlainQueue extends SqsQueue
	{
		protected $messageHandler;

		/**
		 * Create a new Amazon Plain SQS queue instance.
		 *
		 * @param \Aws\Sqs\SqsClient $sqs
		 * @param string $messageHandler
		 * @param string $default
		 * @param string $prefix
		 */
		public function __construct(SqsClient $sqs, $messageHandler, $default, $prefix = '') {
			parent::__construct($sqs, $default, $prefix);

			$this->messageHandler = $messageHandler;
		}

		/**
		 * @return string
		 */
		public function getMessageHandler() {
			return $this->messageHandler;
		}


		/**
		 * @inheritdoc
		 */
		public function pop($queue = null) {
			$queue    = $this->getQueue($queue);
			$response = $this->sqs->receiveMessage([
				'QueueUrl'            => $queue,
				'AttributeNames'      => ['ApproximateReceiveCount'],
			]);


			if (!is_null($response['Messages']) && count($response['Messages']) > 0)
				return new SqsPlainJob($this->container, $this->sqs, $response['Messages'][0], $this->connectionName, $queue, $this->messageHandler);


			return null;
		}


	}