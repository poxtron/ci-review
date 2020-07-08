<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class PhpcsScanSingleFileTest extends TestCase {
	var $options_phpcs
		= [
			'phpcs-path'                      => null,
			'phpcs-standard'                  => null,
			'phpcs-severity'                  => null,
			'phpcs-runtime-set'               => null,
			'commit-test-phpcs-scan-commit-1' => null,
		];

	var $options_git_repo
		= [
			'repo-owner'      => null,
			'repo-name'       => null,
			'git-path'        => null,
			'github-repo-url' => null,
		];

	/**
	 * @covers ::vipgoci_phpcs_scan_single_file
	 */
	public function testDoScanTest1() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			[ 'phpcs-runtime-set', 'github-token', 'token' ],
			$this
		);

		if ( - 1 === $options_test ) {
			return;
		}

		$this->options['commit']
			= $this->options['commit-test-phpcs-scan-commit-1'];

		vipgoci_unittests_output_suppress();

		$this->options['local-git-repo']
			= vipgoci_unittests_setup_git_repo(
			$this->options
		);

		if ( false === $this->options['local-git-repo'] ) {
			$this->markTestSkipped(
				'Could not set up git repository: ' . vipgoci_unittests_output_get()
			);

			return;
		}

		$scan_results = vipgoci_phpcs_scan_single_file(
			$this->options,
			'my-test-file-1.php'
		);

		vipgoci_unittests_output_unsuppress();

		$expected_results = [
			'file_issues_arr_master' => [
				'totals' => [
					'errors'   => 3,
					'warnings' => 0,
					'fixable'  => 0,
				],

				'files' => [
					$scan_results['temp_file_name'] => [
						'errors'   => 3,
						'warnings' => 0,
						'messages' => [
							[
								'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
								'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
								'severity' => 5,
								'fixable'  => false,
								'type'     => 'ERROR',
								'line'     => 3,
								'column'   => 20,
							],

							[
								'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
								'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
								'severity' => 5,
								'fixable'  => false,
								'type'     => 'ERROR',
								'line'     => 7,
								'column'   => 20,
							],

							[
								'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
								'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
								'severity' => 5,
								'fixable'  => false,
								'type'     => 'ERROR',
								'line'     => 11,
								'column'   => 20,
							],

						]

					]
				]
			],

			'file_issues_str' => '',
			'temp_file_name'  => $scan_results['temp_file_name'],
		];

		$expected_results['file_issues_str'] = json_encode(
			$expected_results['file_issues_arr_master']
		);

		$this->assertEquals(
			$expected_results,
			$scan_results
		);
	}

	protected function setUp() {
		vipgoci_unittests_get_config_values(
			'git',
			$this->options_git_repo
		);

		vipgoci_unittests_get_config_values(
			'phpcs-scan',
			$this->options_phpcs
		);

		$this->options_phpcs['phpcs-sniffs-exclude'] = [];

		$this->options = array_merge(
			$this->options_git_repo,
			$this->options_phpcs
		);

		$this->options['github-token']
			= vipgoci_unittests_get_config_value(
			'git-secrets',
			'github-token',
			true // Fetch from secrets file
		);

		$this->options['token']
			= $this->options['github-token'];

		$this->options['branches-ignore'] = [];

		$this->options['svg-checks'] = false;

		$this->options['lint-skip-folders'] = [];

		$this->options['phpcs-skip-folders'] = [];
	}

	protected function tearDown() {
		if ( false !== $this->options['local-git-repo'] ) {
			vipgoci_unittests_remove_git_repo(
				$this->options['local-git-repo']
			);
		}

		$this->options_phpcs    = null;
		$this->options_git_repo = null;
		$this->options          = null;
	}
}

