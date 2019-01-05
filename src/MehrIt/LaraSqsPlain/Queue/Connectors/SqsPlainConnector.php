<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:56
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Connectors;


	use Illuminate\Contracts\Queue\Queue;
	use MehrIt\LaraSqsExt\Queue\Connectors\SqsExtConnector;
	use MehrIt\LaraSqsPlain\Queue\SqsPlainQueue;

	class SqsPlainConnector extends SqsExtConnector
	{
		const DEFAULT_QUEUE_TYPE = SqsPlainQueue::class;

		/**
		 * Establish a queue connection.
		 *
		 * @param  array $config
		 * @return Queue|SqsPlainQueue
		 */
		public function connect(array $config) {

			if (empty($config['message_handler']))
				throw new \InvalidArgumentException("Missing configuration option \"message_handler\" for queue \"${config['queue']}\"");

			return parent::connect($config);
		}

	}