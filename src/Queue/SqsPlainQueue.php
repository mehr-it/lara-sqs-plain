<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 13:46
	 */

	namespace MehrIt\LaraSqsPlain\Queue;

	use MehrIt\LaraSqsExt\Queue\SqsExtQueue;
	use MehrIt\LaraSqsPlain\Queue\Jobs\SqsPlainJob;

	class SqsPlainQueue extends SqsExtQueue
	{
		const DEFAULT_JOB_TYPE = SqsPlainJob::class;
	}