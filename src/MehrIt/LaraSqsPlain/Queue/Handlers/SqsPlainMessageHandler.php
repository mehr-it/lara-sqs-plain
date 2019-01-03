<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 15:45
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Handlers;



	use Illuminate\Queue\InteractsWithQueue;

	class SqsPlainMessageHandler
	{
		use InteractsWithQueue;

		/**
		 * @var string
		 */
		protected $message;

		/**
		 * SqsPlainMessageHandler constructor.
		 * @param string $message The raw SQS message
		 */
		public function __construct($message) {
			$this->message = $message;
		}


	}