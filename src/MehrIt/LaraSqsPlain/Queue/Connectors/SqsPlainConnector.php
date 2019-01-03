<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:56
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Connectors;


	use Aws\Sqs\SqsClient;
	use Illuminate\Queue\Connectors\SqsConnector;
	use Illuminate\Support\Arr;
	use MehrIt\LaraSqsPlain\Queue\SqsPlainQueue;

	class SqsPlainConnector extends SqsConnector
	{
		/**
		 * Establish a queue connection.
		 *
		 * @param  array $config
		 * @return \Illuminate\Contracts\Queue\Queue
		 */
		public function connect(array $config) {
			$config = $this->getDefaultConfiguration($config);
			if ($config['key'] && $config['secret']) {
				$config['credentials'] = Arr::only($config, ['key', 'secret']);
			}

			if (empty($config['message_handler']))
				throw new \InvalidArgumentException("Missing configuration option \"message_handler\" for queue \"${config['queue']}\"");

			return new SqsPlainQueue(
				new SqsClient($config), $config['message_handler'], $config['queue'], Arr::get($config, 'prefix', '')
			);
		}

	}