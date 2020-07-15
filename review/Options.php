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
//		'commit:', // TODO send commit ref to check if local repo is in correct HEAD
	];

	private static $instance = null;

	private $options = [];

	/**
	 * Options constructor.
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
		if ( DIRECTORY_SEPARATOR === substr( trim( $this->options['repo-path'] ), - 1 ) ) {
			$this->options['repo-path'] = substr( trim( $this->options['repo-path'] ), 0, - 1 );
		}

		if ( ! file_exists( $this->options['repo-path'] ) ) {
			throw new Exception( 'Repo path is not valid.' );
		}


//		print_r( $this->options );
	}

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