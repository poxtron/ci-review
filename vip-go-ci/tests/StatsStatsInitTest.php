<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class StatsStatsInitTest extends TestCase {
	/**
	 * @covers ::vipgoci_stats_init
	 */
	public function testStatsInit() {
		$pr_item1         = new stdClass();
		$pr_item1->number = 100;

		$pr_item2         = new stdClass();
		$pr_item2->number = 110;

		$stats_arr = [];

		vipgoci_stats_init(
			[
				'phpcs'      => true,
				'lint'       => true,
				'hashes-api' => false
			],
			[
				$pr_item1,
				$pr_item2
			],
			$stats_arr
		);

		return $this->assertEquals(
			[
				'issues' => [
					100 => [],

					110 => [],
				],

				'stats' => [
					VIPGOCI_STATS_PHPCS => [
						100 => [
							'error'   => 0,
							'warning' => 0,
							'info'    => 0,
						],

						110 => [
							'error'   => 0,
							'warning' => 0,
							'info'    => 0,
						],
						// no hashes-api; not supposed to initialize that
					],

					VIPGOCI_STATS_LINT => [
						100 => [
							'error'   => 0,
							'warning' => 0,
							'info'    => 0,
						],

						110 => [
							'error'   => 0,
							'warning' => 0,
							'info'    => 0,
						],
						// no hashes-api; not supposed to initialize that
					],
				]
			],
			$stats_arr
		);
	}
}

