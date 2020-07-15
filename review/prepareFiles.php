<?php

namespace ET\PR_Review;

use Exception;


class prepareFiles {
	private static $instance = null;

	private $filesDir = '';

	private $diffResults = [];

	/**
	 * Run constructor.
	 * @throws Exception
	 */
	private function __construct() {
		$files = array_keys( $this->parseDiff() );

		// create tmp dir
		$tempfile = tempnam( sys_get_temp_dir(), '' );
		unlink( $tempfile );
		$tempdir = "{$tempfile}data";
		mkdir( $tempdir );

		// copy files with directory structure to tmp dir
		foreach ( $files as $file ) {
			$fileNameArray = explode( '.', $file );
			$extension     = end( $fileNameArray );
			if ( in_array( $extension, [ 'php', 'js', 'jsx', 'ts' ] ) ) {
				exec( "cd " . Options::get( 'repo-path' ) . " && cp --parents $file $tempdir" );
			}
		}

		$this->filesDir = $tempdir;
	}

	private function parseDiff() {
		$diffArray = githubAPI::getDiff();

		$position      = 0;
		$lineNumber    = 0;
		$modifiedFiles = [];
		$currentFile   = '';

		$result = [];

		foreach ( $diffArray as $line ) {
			// only look at lines in diff that we care
			if ( in_array( $line[0], [ '+', '@', ' ', '-' ] ) ) {

				// old file name is not important.
				if ( $line[0] . $line[1] === '--' ) {
					continue;
				}

				// get new file name and reset diff position.
				if ( $line[0] . $line[1] === '++' ) {
					$currentFile = trim( str_replace( [ '+++', ' b/' ], '', $line ) );
					$position    = - 1;
				}

				// reset line number using hunk indicator.
				if ( $line[0] . $line[1] === '@@' ) {
					$lineArray  = explode( ' ', $line );
					$lineNumber = explode( ',', ltrim( $lineArray[2], '+' ) )[0];
				}

				// store only modified lines with line number and position on diff.
				if ( $currentFile !== '/dev/null' && $position > 0 && $line[0] . $line[1] !== '@@' && $line[0] === '+' ) {
					// TODO remove below legacy/testing string
					array_push( $modifiedFiles, "$currentFile:$lineNumber $position" . ' ' . trim( $line ) );

					if ( ! isset( $result[ $currentFile ] ) ) {
						$result[ $currentFile ] = [];
					}
					$result[ $currentFile ] = array_merge( $result[ $currentFile ], [ "+$lineNumber" => $position ] );
				}

				// increase line number if current line is modified or new.
				if ( ! in_array( $line[0], [ '-', '@' ] ) ) {
					$lineNumber ++;
				}

				// increase diff position needed for commenting.
				$position ++;
			}
		}

		$this->diffResults = $result;

		return $this->diffResults;
	}

	static function getFilesDir() {
		return self::instance()->filesDir;
	}

	/**
	 * @return prepareFiles
	 */
	static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	static function getDiffResults() {
		return self::instance()->diffResults;
	}
}