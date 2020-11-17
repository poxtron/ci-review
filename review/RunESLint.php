<?php

namespace ET\PR_Review;

class RunESLint {
	private static $instance = null;

	private $results = [];

	/**
	 * RunPhpcs constructor.
	 */
	private function __construct() {
		$tmpDir     = PrepareFiles::getFilesDir();
		$filesLines = [];

		foreach ( PrepareFiles::getDiffResults() as $file => $data ) {
			foreach ( $data as $line => $position ) {
				array_push( $filesLines, "$file:" . ltrim( $line, '+' ) );
			}
		}

		// run eslint on tmp dir
		$eslint       = realpath( 'eslint/node_modules/.bin/eslint' );
		$config       = realpath( 'eslint/.eslintrc.json' );
		$tmpJson      = $tmpDir . DIRECTORY_SEPARATOR . 'report.json';
		$phpcsCommand = ".$eslint -c $config \"$tmpDir/**\" -f json > $tmpJson";

		// echo $phpcsCommand . PHP_EOL; die;

		exec( $phpcsCommand, $cmd_result );



		$fileArray = json_decode( file_get_contents( $tmpJson ), true );

		$errors   = 0;
		$warnings = 0;
		$results  = [];

		foreach ( $fileArray as $file ) {
			if ( ! empty( $file['messages'] ) ) {
				$cleanName = str_replace( $tmpDir . DIRECTORY_SEPARATOR, '', $file['filePath'] );
				foreach ( $file['messages'] as $message ) {
					if ( in_array( "$cleanName:{$message['line']}", $filesLines ) ) {
						if ( ! isset( $results[ $cleanName ] ) ) {
							$results[ $cleanName ] = [];
						}
						$results[ $cleanName ][] = $message;
						if ( 1 == $message['severity'] ) {
							$warnings++;
						} else {
							$errors++;
						}
					}
				}
			}
		}

		$this->results = [
			'results'  => $results,
			'errors'   => $errors,
			'warnings' => $warnings,
		];

		// foreach ( $fileArray['files'] as $fileName => $fileResults ) {
		// 	if ( ! empty( $fileResults['messages'] ) ) {
		// 		$cleanName = str_replace( $tmpDir . DIRECTORY_SEPARATOR, '', $fileName );
		// 		foreach ( $fileResults['messages'] as $message ) {
		// 			if ( in_array( "$cleanName:{$message['line']}", $filesLines ) ) {
		// 				if ( ! isset( $results[ $cleanName ] ) ) {
		// 					$results[ $cleanName ] = [];
		// 				}
		// 				$results[ $cleanName ][] = $message;
		// 				if ( $message['type'] === 'WARNING' ) {
		// 					$warnings++;
		// 				} else {
		// 					$errors++;
		// 				}
		// 			}
		// 		}
		// 	}
		// }
		//
		// foreach ( $cmd_result as $result ) {
		// 	$resultArray = explode( ',', $result );
		// 	$fileLine    = str_replace( ' line ', '', $resultArray[0] );
		// 	$cleanName   = str_replace( $tmpDir . DIRECTORY_SEPARATOR, '', $fileLine );
		//
		// }

		//./eslint -c ../../.eslintrc.json  ../../../review/tests/js -f compact

		/*
		/home/miguel/ET/ci-review/review/tests/js/file_with_errors.js: line 1, col 1, Error - Expected an assignment or function call and instead saw an expression. (no-unused-expressions)
		/home/miguel/ET/ci-review/review/tests/js/file_with_errors.js: line 1, col 1, Error - 'dsfsdf' is not defined. (no-undef)
		/home/miguel/ET/ci-review/review/tests/js/file_with_errors.js: line 1, col 7, Error - Missing semicolon. (semi)
		/home/miguel/ET/ci-review/review/tests/js/file_with_errors.js: line 2, col 1, Error - Too many blank lines at the end of file. Max of 0 allowed. (no-multiple-empty-lines)
		 */

	}

	static function getResults() {
		return self::instance()->results;
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