<?php

namespace ET\PR_Review;

use stdClass;

class GitHubAPI {

	const URL = 'https://api.github.com';

	const SLEEP = true;

	static function getDiff( $remote = true ) {
		// create temporary file to store diff from local or remote source
		$tempfile = tempnam( sys_get_temp_dir(), '' );

		if ( $remote ) {
			$pRId      = Options::get( 'pr-id' );
			$repoOwner = Options::get( 'repo-owner' );
			$repoName  = Options::get( 'repo-name' );

			$url = self::URL;

			$headersString = self::curlHeaders( [
				'"Accept: application/vnd.github.v3.diff"',
			] );

			$command = "curl -s \"$url/repos/$repoOwner/$repoName/pulls/$pRId\" $headersString | cat > $tempfile";

			exec( $command, $none );

			self::maybeSleep();
		} else {
			$repoPath   = Options::get( 'repo-path' );
			$baseBranch = Options::get( 'base-branch' );
			$command    = "cd $repoPath && git diff --output=$tempfile $baseBranch";

			exec( $command, $none );
		}

		return file( $tempfile );
	}

	static function curlHeaders( $extra = [] ) {
		$token   = Options::get( 'token' );
		$headers = array_merge( $extra, [
			"\"Authorization: Bearer $token\"",
		] );

		return '-H ' . implode( ' -H ', $headers );
	}

	static function maybeSleep() {
		if ( self::SLEEP ) {
			sleep( 2 );
		}
	}

	static function createReview() {
		$phpcs    = RunPhpcs::getResults();
		$errors   = $phpcs['errors'] > 0 ? ":no_entry_sign: {$phpcs['errors']} Errors\n\r" : '';
		$warnings = $phpcs['warnings'] > 0 ? ":warning: {$phpcs['warnings']} Warnings\n\r" : '';

		// payload json
		$payload            = new stdClass();
		$payload->commit_id = Options::get( 'commit' );
		$payload->body      = "**phpcs** results:\n\r$errors$warnings";
		$payload->event     = $phpcs['errors'] + $phpcs['warnings'] > 0 ? 'REQUEST_CHANGES' : 'APPROVE';

		if ( 'REQUEST_CHANGES' === $payload->event ) {
			$payload->comments = [];

			$diffResults = PrepareFiles::getDiffResults();

			foreach ( $phpcs['results'] as $file => $messages ) {
				if ( isset( $diffResults[ $file ] ) ) {
					foreach ( $messages as $message ) {
						$line = "+{$message['line']}";
						$type = 'WARNING' === $message['type'] ? ':warning:' : ':no_entry_sign:';
						// TODO merge messages on the same line
						if ( isset( $diffResults[ $file ][ $line ] ) ) {
							$comment             = [];
							$comment['path']     = $file;
							$comment['position'] = $diffResults[ $file ][ $line ];
							$comment['body']     = "$type {$message['message']}";
							array_push( $payload->comments, (object) $comment );
						}
					}
				}
			}

			$payloadJSON   = json_encode( $payload );
			$headersString = self::curlHeaders( [
				'"Content-Type: application/json"',
			] );
			$pRId          = Options::get( 'pr-id' );
			$repoOwner     = Options::get( 'repo-owner' );
			$repoName      = Options::get( 'repo-name' );
			$url           = self::URL;

			$command = "curl -s -d '$payloadJSON' $headersString -X POST $url/repos/$repoOwner/$repoName/pulls/$pRId/reviews";

			exec( $command, $execResult );

			echo implode( "\n", $execResult );

			self::maybeSleep();

			// Tell GitHub actions that the action has errors or warnings.
			exit( 1 );
		}
	}
}