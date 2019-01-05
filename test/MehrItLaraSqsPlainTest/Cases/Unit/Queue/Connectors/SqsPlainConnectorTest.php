<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 15:17
	 */

	namespace MehrItLaraSqsPlainTest\Cases\Unit\Queue\Connectors;


	use MehrIt\LaraSqsPlain\Queue\Connectors\SqsPlainConnector;
	use MehrIt\LaraSqsPlain\Queue\SqsPlainQueue;
	use MehrItLaraSqsPlainTest\Cases\TestCase;

	class SqsPlainConnectorTest extends TestCase
	{

		public function testConnect() {
			$conn = new SqsPlainConnector();

			/** @var SqsPlainQueue $ret */
			$ret = $conn->connect([
				'driver' => 'sqs',
				'key'    => 'AMAZONSQSKEY',
				'secret' => 'AmAz0n+SqSsEcReT+aLpHaNuM3R1CsTr1nG',
				'queue'  => 'https://sqs.someregion.amazonaws.com/123123123123/QUEUE',
				'region' => 'someregion',
				'message_handler' => 'my_handler'
			]);


			$this->assertInstanceOf(SqsPlainQueue::class, $ret);

		}

		public function testConnect_MessageHandlerNotConfigured() {
			$conn = new SqsPlainConnector();

			$this->expectException(\InvalidArgumentException::class);

			/** @var SqsPlainQueue $ret */
			$conn->connect([
				'driver' => 'sqs',
				'key'    => 'AMAZONSQSKEY',
				'secret' => 'AmAz0n+SqSsEcReT+aLpHaNuM3R1CsTr1nG',
				'queue'  => 'https://sqs.someregion.amazonaws.com/123123123123/QUEUE',
				'region' => 'someregion',
			]);



		}

	}