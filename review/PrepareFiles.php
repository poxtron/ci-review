<?php

namespace ET\PR_Review;

use Exception;

class PrepareFiles {
	private static $instance = null;

	private $filesDir = '';

	private $diffResults = [];

	/**
	 * Create a copy of the codebase only with modified files and store them on a temporary file.
	 *
	 * @throws Exception
	 */
	private function __construct() {
		$files = array_keys( $this->parseDiff() );

		// create tmp dir
		$tmpFile = tempnam( sys_get_temp_dir(), '' );
		unlink( $tmpFile );
		$tmpDir = "{$tmpFile}data";
		mkdir( $tmpDir );

		// copy files with directory structure to tmp dir
		foreach ( $files as $key => $file ) {
			$fileNameArray = explode( '.', $file );
			$extension     = end( $fileNameArray );
			if ( in_array( $extension, [ 'php', 'js', 'jsx' ] ) ) {
				if ( strpos( $file, DIRECTORY_SEPARATOR . 'ShortcodeOutput' . DIRECTORY_SEPARATOR ) !== false ) {
					unset( $this->diffResults[ $file ] );
					unset( $files[ $key ] );
					continue;
				}
				exec( "cd " . Options::get( 'repo-path' ) . " && cp --parents $file $tmpDir" );
			}
		}

		$this->filesDir = $tmpDir;
	}

	/**
	 * @return array Array of modified files line numbers and diff positions.
	 */
	private function parseDiff() {
		$diffArray = GitHubAPI::getDiff();

		$position      = 0;
		$lineNumber    = 0;
		$modifiedFiles = [];
		$currentFile   = '';

		$result = [];

		foreach ( $diffArray as $line ) {
			// Only look at lines in diff that we care.
			if ( in_array( $line[0], [ '+', '@', ' ', '-' ] ) ) {

				// Old file name is not important.
				if ( $line[0] . $line[1] === '--' ) {
					continue;
				}

				// Get new file name and reset diff position.
				if ( $line[0] . $line[1] === '++' ) {
					$currentFile = trim( str_replace( [ '+++', ' b/' ], '', $line ) );
					$position    = -1;
				}

				// Reset line number using hunk indicator.
				if ( $line[0] . $line[1] === '@@' ) {
					$lineArray  = explode( ' ', $line );
					$lineNumber = explode( ',', ltrim( $lineArray[2], '+' ) )[0];
				}

				// Store only modified lines with line number and position on diff.
				if ( $currentFile !== '/dev/null' && $position > 0 && $line[0] . $line[1] !== '@@' && $line[0] === '+' ) {
					// TODO remove below legacy/testing string
					array_push( $modifiedFiles, "$currentFile:$lineNumber $position" . ' ' . trim( $line ) );

					if ( ! isset( $result[ $currentFile ] ) ) {
						$result[ $currentFile ] = [];
					}
					$result[ $currentFile ] = array_merge( $result[ $currentFile ], [ "+$lineNumber" => $position ] );
				}

				// Increase line number if current line is modified or new.
				if ( ! in_array( $line[0], [ '-', '@' ] ) ) {
					$lineNumber++;
				}

				// Increase diff position needed for commenting.
				$position++;
			}
		}

		$this->diffResults = $result;

		return $this->diffResults;
	}

	/**
	 * @return string Temporal dir where the code to be lint is stored, without trailing slash.
	 */
	static function getFilesDir() {
		return self::instance()->filesDir;
	}

	/**
	 * @return PrepareFiles
	 */
	static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * + sign is added to the line number to force string value on array key.
	 *
	 * @return array [ fileName => [ +lineNumber => diffPosition, ...] ...]
	 */
	static function getDiffResults() {
		return self::instance()->diffResults;
	}
}
