<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 15:45
	 */

	namespace MehrIt\LaraSqsPlain\Queue\Handlers;



	use Illuminate\Queue\InteractsWithQueue;
	use MehrIt\LaraSqsExt\Queue\InteractsWithSqsQueue;

	class SqsPlainMessageHandler
	{
		use InteractsWithQueue;
		use InteractsWithSqsQueue;


		/**
		 * The automatic queue visibility time. True will use the job's timeout if specified
		 *
		 * @return int|boolean
		 */
		public $automaticQueueVisibility = true;

		/**
		 * The automatic queue visibility time to add to job timeout
		 *
		 * @var int
		 */
		public $automaticQueueVisibilityExtra = 0;

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