<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class VipgociOptionsBoolHandleTest extends TestCase {
	/**
	 * @covers ::vipgoci_option_bool_handle
	 */
	public function testOptionsBoolHandle1() {
		$options = [];

		vipgoci_option_bool_handle(
			$options,
			'mytestoption',
			'false'
		);

		$this->assertEquals(
			false,
			$options['mytestoption']
		);
	}

	/**
	 * @covers ::vipgoci_option_bool_handle
	 */
	public function testOptionsBoolHandle2() {
		$options = [
			'mytestoption' => 'false',
		];

		vipgoci_option_bool_handle(
			$options,
			'mytestoption',
			false
		);

		$this->assertEquals(
			false,
			$options['mytestoption']
		);
	}

	/**
	 * @covers ::vipgoci_option_bool_handle
	 */
	public function testOptionsBoolHandle3() {
		$options = [
			'mytestoption' => 'true',
		];

		vipgoci_option_bool_handle(
			$options,
			'mytestoption',
			true
		);

		$this->assertEquals(
			true,
			$options['mytestoption']
		);
	}
}
