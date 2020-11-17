<?php

namespace ET\PR_Review;

class RunESLint {
	private static $instance = null;

	private $results = [];

	/**
	 * RunESLint constructor.
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
		$eslint  = __DIR__ . '/eslint/node_modules/.bin/eslint';
		$config  = __DIR__ . '/eslint/.eslintrc.json';
		$tmpJson = $tmpDir . DIRECTORY_SEPARATOR . 'report.json';
		$command = " node $eslint -c $config \"$tmpDir/**\" -f json > $tmpJson";

		exec( $command, $cmd_result );

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
						$cleanMessage            = [];
						$cleanMessage['message'] = $message['message'];
						$cleanMessage['line']    = $message['line'];
						$cleanMessage['source']  = empty( $message['ruleId'] ) ? 'Syntax' : $message['ruleId'];
						$cleanMessage['type']    = 1 == $message['severity'] ? 'WARNING' : 'ERROR';
						$results[ $cleanName ][] = $cleanMessage;
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
