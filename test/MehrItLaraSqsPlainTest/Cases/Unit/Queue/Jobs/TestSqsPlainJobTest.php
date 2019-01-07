<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 15:16
	 */

	namespace MehrItLaraSqsPlainTest\Cases\Unit\Queue\Jobs;

	use Aws\Sqs\SqsClient;
	use Carbon\Carbon;
	use Illuminate\Container\Container;
	use Illuminate\Queue\SqsQueue;
	use MehrIt\LaraSqsPlain\Queue\Handlers\SqsPlainMessageHandler;
	use MehrIt\LaraSqsPlain\Queue\Jobs\SqsPlainJob;
	use MehrItLaraSqsPlainTest\Cases\TestCase;
	use Mockery as m;

	class SqsPlainJobTest extends TestCase
	{
		public function setUp() {
			parent::setUp();

			TestSqsPlainJobHandler::reset();

			$this->key          = 'AMAZONSQSKEY';
			$this->secret       = 'AmAz0n+SqSsEcReT+aLpHaNuM3R1CsTr1nG';
			$this->service      = 'sqs';
			$this->region       = 'someregion';
			$this->account      = '1234567891011';
			$this->queueName    = 'emails';
			$this->baseUrl      = 'https://sqs.someregion.amazonaws.com';
			$this->releaseDelay = 0;
			// This is how the modified getQueue builds the queueUrl
			$this->queueUrl = $this->baseUrl . '/' . $this->account . '/' . $this->queueName;
			// Get a mock of the SqsClient
			$this->mockedSqsClient = $this->getMockBuilder(SqsClient::class)
				->setMethods(['deleteMessage'])
				->disableOriginalConstructor()
				->getMock();
			// Use Mockery to mock the IoC Container
			$this->mockedContainer     = m::mock(Container::class);
			$this->mockedData          = ['data'];
			$this->mockedPayload       = 'data in plain';
			$this->mockedMessageId     = 'e3cd03ee-59a3-4ad8-b0aa-ee2e3808ac81';
			$this->mockedReceiptHandle = '0NNAq8PwvXuWv5gMtS9DJ8qEdyiUwbAjpp45w2m6M4SJ1Y+PxCh7R930NRB8ylSacEmoSnW18bgd4nK\/O6ctE+VFVul4eD23mA07vVoSnPI4F\/voI1eNCp6Iax0ktGmhlNVzBwaZHEr91BRtqTRM3QKd2ASF8u+IQaSwyl\/DGK+P1+dqUOodvOVtExJwdyDLy1glZVgm85Yw9Jf5yZEEErqRwzYz\/qSigdvW4sm2l7e4phRol\/+IjMtovOyH\/ukueYdlVbQ4OshQLENhUKe7RNN5i6bE\/e5x9bnPhfj2gbM';
			$this->mockedJobData       = [
				'Body'          => $this->mockedPayload,
				'MD5OfBody'     => md5($this->mockedPayload),
				'ReceiptHandle' => $this->mockedReceiptHandle,
				'MessageId'     => $this->mockedMessageId,
				'Attributes'    => ['ApproximateReceiveCount' => 1],
			];
			$this->jobHandler          = TestSqsPlainJobHandler::class;
		}

		public function tearDown() {
			m::close();
		}

		public function testFireProperlyCallsTheJobHandler() {
			$job = $this->getJob(false);
			$job->fire();

			$this->assertSame([[$this->mockedPayload]], TestSqsPlainJobHandler::$constructLog);
			$this->assertInstanceOf(SqsPlainJob::class, TestSqsPlainJobHandler::$handledJobs[0]);
			$this->assertSame($this->mockedMessageId, TestSqsPlainJobHandler::$handledJobs[0]->getJobId());
		}

		public function testDeleteRemovesTheJobFromSqs() {
			$this->mockedSqsClient = $this->getMockBuilder(SqsClient::class)
				->setMethods(['deleteMessage'])
				->disableOriginalConstructor()
				->getMock();
			$queue                 = $this->getMockBuilder(SqsQueue::class)->setMethods(['getQueue'])->setConstructorArgs([$this->mockedSqsClient, $this->jobHandler, $this->queueName, $this->account])->getMock();
			$queue->setContainer($this->mockedContainer);
			$job = $this->getJob();
			$job->getSqs()->expects($this->once())->method('deleteMessage')->with(['QueueUrl' => $this->queueUrl, 'ReceiptHandle' => $this->mockedReceiptHandle]);
			$job->delete();
		}

		public function testReleaseProperlyReleasesTheJobOntoSqs() {
			$this->mockedSqsClient = $this->getMockBuilder(SqsClient::class)
				->setMethods(['changeMessageVisibility'])
				->disableOriginalConstructor()
				->getMock();
			$queue                 = $this->getMockBuilder(SqsQueue::class)->setMethods(['getQueue'])->setConstructorArgs([$this->mockedSqsClient, $this->jobHandler, $this->queueName, $this->account])->getMock();
			$queue->setContainer($this->mockedContainer);
			$job = $this->getJob();
			$job->getSqs()->expects($this->once())->method('changeMessageVisibility')->with(['QueueUrl' => $this->queueUrl, 'ReceiptHandle' => $this->mockedReceiptHandle, 'VisibilityTimeout' => $this->releaseDelay]);
			$job->release($this->releaseDelay);
			$this->assertTrue($job->isReleased());
		}

		public function testTriesSet() {
			app()->bind('testHandler', function($app, $params) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->tries = 15;

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame(15, $job->maxTries());
		}

		public function testTimeoutSet() {
			app()->bind('testHandler', function($app, $params) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->timeout = 70;

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame(70, $job->timeout());
		}

		public function testTimeoutAtSet() {
			$dt = new Carbon();

			app()->bind('testHandler', function($app, $params) use ($dt) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->timeoutAt = $dt->getTimestamp();

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame($dt->getTimestamp(), $job->timeoutAt());
		}

		public function testTimeoutAtSetToDateTime() {
			$dt = new Carbon();

			app()->bind('testHandler', function($app, $params) use ($dt) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->timeoutAt = $dt;

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame($dt->getTimestamp(), $job->timeoutAt());
		}

		public function testAutomaticQueueVisibilitySet() {
			app()->bind('testHandler', function ($app, $params) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->automaticQueueVisibility = 14;

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame(14, $job->automaticQueueVisibility());
		}

		public function testAutomaticQueueVisibilityExtraSet() {
			app()->bind('testHandler', function ($app, $params) {
				$handler = new TestSqsPlainJobHandler($params['message']);

				$handler->automaticQueueVisibilityExtra = 3;

				return $handler;
			});

			$job = new SqsPlainJob(app(), $this->mockedSqsClient, $this->mockedJobData, 'connection-name', $this->queueUrl, 'testHandler');

			$this->assertSame(3, $job->automaticQueueVisibilityExtra());
		}

		protected function getJob($mockContainer = true) {
			return new SqsPlainJob(
				$mockContainer ? $this->mockedContainer : app(),
				$this->mockedSqsClient,
				$this->mockedJobData,
				'connection-name',
				$this->queueUrl,
				$this->jobHandler
			);
		}
	}

	class TestSqsPlainJobHandler extends SqsPlainMessageHandler
	{

		public static $constructLog = [];
		public static $handledJobs = [];


		/**
		 * SqsPlainMessageHandler constructor.
		 * @param string $message The raw SQS message
		 */
		public function __construct($message) {
			parent::__construct($message);

			static::$constructLog[] = func_get_args();
		}


		public function handle() {
			static::$handledJobs[] = $this->job;
		}

		public static function reset() {
			static::$constructLog = [];
			static::$handledJobs = [];
		}

	}