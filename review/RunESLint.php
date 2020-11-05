<?php

namespace ET\PR_Review;

class RunESLint {
	private static $instance = null;

	private $results = [];

	/**
	 * RunPhpcs constructor.
	 */
	private function __construct() {

	}

	/**
	 * @return RunESLint
	 */
	static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}