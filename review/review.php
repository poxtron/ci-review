#!/usr/bin/env php
<?php

namespace ET\PR_Review;

use Exception;

// Only run on cli
if ( 'cli' !== php_sapi_name() ) {
	die( 'This script can only run on CLI' );
}

// Autoloader.
spl_autoload_register( __NAMESPACE__ . '\auto_loader' );

/**
 * Automatically includes classes on invocation.
 * For example: <code>SomeClass::instance();</code> will automatically include <code>SomeClass.php</code>
 *
 * @param string $class_name Class name to load.
 *
 * @return void
 */
function auto_loader( $class_name ) {
	if ( false !== strpos( $class_name, __NAMESPACE__ ) ) {
		$dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		require_once $dir . str_replace( [ __NAMESPACE__, '\\' ], '', $class_name ) . '.php';
	}
}

function do_eslint() {
	var_dump(Options::get('do_eslint'));
	return 'true' === Options::get('do_eslint');
}

try {
	GitHubAPI::createReview();
	exit( 0 );
} catch ( Exception $exception ) {
	echo "ERROR: \n" . $exception->getMessage() . "\n";
	exit( 1 );
}
