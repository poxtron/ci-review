<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class PhpcsScanScanCommitTest extends TestCase {
	var $options_phpcs
		= [
			'phpcs-path'                      => null,
			'phpcs-standard'                  => null,
			'phpcs-severity'                  => null,
			'phpcs-runtime-set'               => null,
			'commit-test-phpcs-scan-commit-1' => null,
			'commit-test-phpcs-scan-commit-2' => null,
			'commit-test-phpcs-scan-commit-4' => null,
		];

	var $options_git_repo
		= [
			'repo-owner'      => null,
			'repo-name'       => null,
			'git-path'        => null,
			'github-repo-url' => null,
		];

	/**
	 * @covers ::vipgoci_phpcs_scan_commit
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

		$this->options['phpcs-skip-scanning-via-labels-allowed']
			= false;

		$issues_submit = [];
		$issues_stats  = [];

		vipgoci_unittests_output_suppress();

		$prs_implicated = vipgoci_github_prs_implicated(
			$this->options['repo-owner'],
			$this->options['repo-name'],
			$this->options['commit'],
			$this->options['github-token'],
			$this->options['branches-ignore']
		);

		foreach ( $prs_implicated as $pr_item ) {
			$issues_stats[ $pr_item->number ]['error'] = 0;
		}

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

		vipgoci_phpcs_scan_commit(
			$this->options,
			$issues_submit,
			$issues_stats
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertEquals(
			[
				8 => [
					[
						'type'      => 'phpcs',
						'file_name' => 'my-test-file-1.php',
						'file_line' => 3,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 3,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'my-test-file-1.php',
						'file_line' => 7,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 7,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'my-test-file-1.php',
						'file_line' => 11,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 11,
							'column'   => 20,
							'level'    => 'ERROR'
						]
					]
				]
			],

			$issues_submit
		);

		$this->assertEquals(
			[
				8 => [
					'error' => 3,
				]
			],
			$issues_stats
		);
	}

	/**
	 * @covers ::vipgoci_phpcs_scan_commit
	 */
	public function testDoScanTest2() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			[ 'phpcs-runtime-set', 'github-token', 'token' ],
			$this
		);

		if ( - 1 === $options_test ) {
			return;
		}

		$this->options['commit']
			= $this->options['commit-test-phpcs-scan-commit-2'];

		/*
		 * Skipping PHPCS scanning via PR labels is allowed,
		 * one PR should be set up to ask to skip but another
		 * should not.
		 */
		$this->options['phpcs-skip-scanning-via-labels-allowed']
			= true;

		$issues_submit = [];
		$issues_stats  = [];

		vipgoci_unittests_output_suppress();

		$prs_implicated = vipgoci_github_prs_implicated(
			$this->options['repo-owner'],
			$this->options['repo-name'],
			$this->options['commit'],
			$this->options['github-token'],
			$this->options['branches-ignore']
		);

		foreach ( $prs_implicated as $pr_item ) {
			$issues_stats[ $pr_item->number ]['error'] = 0;
		}

		vipgoci_unittests_output_unsuppress();

		/*
		 * We should have found two PRs, and
		 * we should have initialised statistics
		 * for both. Make sure it is so.
		 */
		$this->assertEquals(
			[
				21 => [
					'error' => 0,
				],

				22 => [
					'error' => 0,
				],
			],
			$issues_stats
		);

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

		vipgoci_phpcs_scan_commit(
			$this->options,
			$issues_submit,
			$issues_stats
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertEquals(
			[
				21 => [
					[
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 3,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 3,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 7,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 7,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],
				],

				/*
				 * No errors for PR #22 because
				 * label is set to skip PHPCS scanning.
				 */
			],

			$issues_submit
		);

		$this->assertEquals(
			[
				21 => [
					'error' => 2,
				],

				/*
				 * Statistics for this scan-type gets
				 * removed when PHPCS scanning is skipped,
				 * so don't expect anything for PR #22.
				 */
			],
			$issues_stats
		);
	}

	/**
	 * @covers ::vipgoci_phpcs_scan_commit
	 */
	public function testDoScanTest3() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			[ 'phpcs-runtime-set', 'github-token', 'token' ],
			$this
		);

		if ( - 1 === $options_test ) {
			return;
		}

		$this->options['commit']
			= $this->options['commit-test-phpcs-scan-commit-4'];

		$this->options['phpcs-skip-scanning-via-labels-allowed']
			= false;

		$this->options['lint-skip-folders'] = [];

		$this->options['phpcs-skip-folders'] = [
			'tests2',
			'tests3',
			'tests4',
		];

		$issues_submit = [];
		$issues_stats  = [];

		vipgoci_unittests_output_suppress();

		$prs_implicated = vipgoci_github_prs_implicated(
			$this->options['repo-owner'],
			$this->options['repo-name'],
			$this->options['commit'],
			$this->options['github-token'],
			$this->options['branches-ignore']
		);

		foreach ( $prs_implicated as $pr_item ) {
			$issues_stats[ $pr_item->number ]['error'] = 0;

			$issues_stats[ $pr_item->number ]['warning'] = 0;
		}

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

		vipgoci_phpcs_scan_commit(
			$this->options,
			$issues_submit,
			$issues_stats
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertEquals(
			[
				30 => [
					[
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 3,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 3,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 7,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 7,
							'column'   => 20,
							'level'    => 'ERROR',
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 10,
						'issue'     => [
							'message'  => "Scripts should be registered/enqueued via `wp_enqueue_script`. This can improve the site's performance due to script concatenation.",
							'source'   => 'WordPress.WP.EnqueuedResources.NonEnqueuedScript',
							'severity' => 3,
							'fixable'  => false,
							'type'     => 'WARNING',
							'line'     => 10,
							'column'   => 6,
							'level'    => 'WARNING'
						]
					],

					[
						'type'      => 'phpcs',
						'file_name' => 'tests1/some_phpcs_issues.php',
						'file_line' => 3,
						'issue'     => [
							'message'  => "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'time'.",
							'source'   => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
							'severity' => 5,
							'fixable'  => false,
							'type'     => 'ERROR',
							'line'     => 3,
							'column'   => 20,
							'level'    => 'ERROR'
						]
					]

					/*
					 * Note: tests2 folder is skipped from
					 * scanning, so no results for that
					 */
				]
			],

			$issues_submit
		);

		$this->assertEquals(
			[
				30 => [
					'error'   => 3,
					'warning' => 1,
				],
			],
			$issues_stats
		);
	}

	/**
	 * @covers ::vipgoci_phpcs_scan_commit
	 *
	 * Test if --phpcs-sniffs-exclude is used
	 * while doing PHPCS scanning.
	 */
	public function testDoScanTest4() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			[ 'phpcs-runtime-set', 'github-token', 'token' ],
			$this
		);

		if ( - 1 === $options_test ) {
			return;
		}

		$this->options['commit']
			= $this->options['commit-test-phpcs-scan-commit-4'];

		$this->options['phpcs-skip-scanning-via-labels-allowed']
			= false;

		$this->options['lint-skip-folders'] = [];

		$this->options['phpcs-skip-folders'] = [];

		// Sniff to skip.
		$this->options['phpcs-sniffs-exclude'] = [
			'WordPress.Security.EscapeOutput',
		];

		$issues_submit = [];
		$issues_stats  = [];

		vipgoci_unittests_output_suppress();

		$prs_implicated = vipgoci_github_prs_implicated(
			$this->options['repo-owner'],
			$this->options['repo-name'],
			$this->options['commit'],
			$this->options['github-token'],
			$this->options['branches-ignore']
		);

		vipgoci_unittests_output_unsuppress();

		foreach ( $prs_implicated as $pr_item ) {
			$issues_stats[ $pr_item->number ]['warning'] = 0;

			$issues_stats[ $pr_item->number ]['error'] = 0;
		}

		vipgoci_unittests_output_suppress();

		$this->options['local-git-repo']
			= vipgoci_unittests_setup_git_repo(
			$this->options
		);

		vipgoci_unittests_output_unsuppress();

		if ( false === $this->options['local-git-repo'] ) {
			$this->markTestSkipped(
				'Could not set up git repository: ' . vipgoci_unittests_output_get()
			);

			return;
		}

		vipgoci_unittests_output_suppress();

		vipgoci_phpcs_scan_commit(
			$this->options,
			$issues_submit,
			$issues_stats
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertEquals(
			[
				30 => [
					/*
					 * Note: Escaping issues not listed, as
					 * they should have been excluded.
					 */ [
						'type'      => 'phpcs',
						'file_name' => 'test.php',
						'file_line' => 10,
						'issue'     => [
							'message'  => "Scripts should be registered/enqueued via `wp_enqueue_script`. This can improve the site's performance due to script concatenation.",
							'source'   => 'WordPress.WP.EnqueuedResources.NonEnqueuedScript',
							'severity' => 3,
							'fixable'  => false,
							'type'     => 'WARNING',
							'line'     => 10,
							'column'   => 6,
							'level'    => 'WARNING'
						]
					]
				]
			],

			$issues_submit
		);

		$this->assertEquals(
			[
				30 => [
					'error'   => 0,
					'warning' => 1,
				]
			],
			$issues_stats
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
