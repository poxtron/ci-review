<?php

namespace ET\PR_Review;

class RunPhpcs {

	private static $instance = null;

	private $results = [];

	/**
	 * RunPhpcs constructor.
	 */
	private function __construct() {
		$tempdir    = PrepareFiles::getFilesDir();
		$filesLines = [];

		foreach ( PrepareFiles::getDiffResults() as $file => $data ) {
			foreach ( $data as $line => $position ) {
				array_push( $filesLines, "$file:" . ltrim( $line, '+' ) );
			}
		}

		// run phpcs on tmp dir
		$phpcs        = Options::get( 'phpcs-path' );
		$standard     = Options::get( 'phpcs-standard' );
		$tmpJson      = $tempdir . DIRECTORY_SEPARATOR . 'report.json';
		$phpcsCommand = "$phpcs --standard=$standard --report=json --report-file=$tmpJson $tempdir";
		exec( escapeshellcmd( $phpcsCommand ), $result );

		// filter phpcs json results to only get modified lines
		$fileArray = json_decode( file_get_contents( $tmpJson ), true );
		$errors    = 0;
		$warnings  = 0;
		$results   = [];

		foreach ( $fileArray['files'] as $fileName => $fileResults ) {
			if ( ! empty( $fileResults['messages'] ) ) {
				$cleanName = str_replace( $tempdir . DIRECTORY_SEPARATOR, '', $fileName );
				foreach ( $fileResults['messages'] as $message ) {
					if ( in_array( "$cleanName:{$message['line']}", $filesLines ) ) {
						if ( ! isset( $results[ $cleanName ] ) ) {
							$results[ $cleanName ] = [];
						}
						$results[ $cleanName ][] = $message;
						if ( $message['type'] === 'WARNING' ) {
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
	 * @return RunPhpcs
	 */
	static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
