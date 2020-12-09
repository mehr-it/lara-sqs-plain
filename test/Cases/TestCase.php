<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.01.19
	 * Time: 14:29
	 */

	namespace MehrItLaraSqsPlainTest\Cases;


	use MehrIt\LaraSqsPlain\Provider\SqsPlainServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		/**
		 * Get package providers.
		 *
		 * @param  \Illuminate\Foundation\Application $app
		 *
		 * @return array
		 */
		protected function getPackageProviders($app) {
			return [
				SqsPlainServiceProvider::class
			];
		}

	}