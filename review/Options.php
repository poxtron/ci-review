<?php

namespace ET\PR_Review;

use Exception;

class Options {
	const OPTIONS = [
		'repo-owner:',
		'repo-name:',
		'repo-path:',
		'token:',
		'phpcs-path:',
		'phpcs-standard:',
		'base-branch:',
		'pr-id:',
		'commit:',
		'do_eslint:',
	];

	private static $instance = null;

	private $options = [];

	/**
	 * Obtain all options passed to the script, check for required options and sanitize where needed.
	 *
	 * @throws Exception
	 */
	private function __construct() {
		$this->options = getopt( null, self::OPTIONS );

		$requiredError = '';
		foreach ( self::OPTIONS as $option ) {
			$optionName = str_replace( ':', '', $option );
			if ( ! isset( $this->options[ $optionName ] ) ) {
				$requiredError .= "--$optionName= is required \n";
			}
		}
		if ( ! empty( $requiredError ) ) {
			throw new Exception( $requiredError );
		}

		// Sanitization
		if ( DIRECTORY_SEPARATOR === substr( trim( $this->options['repo-path'] ), -1 ) ) {
			$this->options['repo-path'] = substr( trim( $this->options['repo-path'] ), 0, -1 );
		}

		$this->options['do_eslint'] = 'true' === $this->options['do_eslint'];

		if ( ! file_exists( $this->options['repo-path'] ) ) {
			throw new Exception( 'Repo path is not valid.' );
		}
	}

	/**
	 * @param null|string $option Option name to be retrieved.
	 *
	 * @return false|false[]|mixed|string|string[] Option value.
	 */
	static function get( $option = null ) {
		$options = self::instance()->options;

		if ( $option !== null && isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $options;
	}

	/**
	 * @return Options
	 */
	static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
