<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class VipgociExitStatusTest extends TestCase {
	/**
	 * @covers ::vipgoci_exit_status
	 */
	public function testExitStatus1() {
		$exit_status = vipgoci_exit_status(
			[
				'stats' => [
					'lint' => [
						25 => [
							'error' => 0,
						]
					]
				]
			]
		);

		$this->assertEquals(
			0,
			$exit_status
		);
	}

	/**
	 * @covers ::vipgoci_exit_status
	 */
	public function testExitStatus2() {
		$exit_status = vipgoci_exit_status(
			[
				'stats' => [
					'lint' => [
						25 => [
							'error' => 30
						]
					]
				]
			]
		);

		$this->assertEquals(
			250,
			$exit_status
		);
	}
}
