<?php

namespace ET\PR_Review;

use stdClass;

class GitHubAPI {

	const URL = 'https://api.github.com';

	const SLEEP = true;

	static $userName = '';

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

	static function getTokenUsername() {
		if ( ! empty( self::$userName ) ) {
			return self::$userName;
		}

		$headersString = self::curlHeaders();
		$url           = self::URL;

		$command = "curl -s $headersString -X GET $url/user";
		exec( $command, $execResult );
		$userData = json_decode( implode( "\n", $execResult ), true );

		self::$userName = $userData['login'];

		return self::$userName;
	}

	static function deletePRComments() {
		$botUsername   = self::getTokenUsername();
		$headersString = self::curlHeaders();
		$pRId          = Options::get( 'pr-id' );
		$repoOwner     = Options::get( 'repo-owner' );
		$repoName      = Options::get( 'repo-name' );
		$url           = self::URL;

		$command = "curl -s $headersString -X GET $url/repos/$repoOwner/$repoName/pulls/$pRId/comments";
		exec( $command, $execResultComments );

		$comments = json_decode( implode( "\n", $execResultComments ), true );

		if ( count( $comments ) <= 0 ) {
			return;
		}

		foreach ( $comments as $comment ) {
			if ( $botUsername === $comment['user']['login'] ) {
				// Delete all comments made by the bot on this PR.
				$command = "curl -s $headersString -X DELETE $url/repos/$repoOwner/$repoName/pulls/comments/{$comment['id']}";
				exec( $command, $execResult );
				$result = empty( $execResult ) ? '' : print_r( $execResult, true ) . PHP_EOL;
				echo "Deleted comment {$comment['id']}" . PHP_EOL . $result;
			}
		}
	}

	static function createReview() {
		$phpcs    = RunPhpcs::getResults();
		$errors   = $phpcs['errors'] > 0 ? ":no_entry_sign: {$phpcs['errors']} Errors\n\r" : '';
		$warnings = $phpcs['warnings'] > 0 ? ":warning: {$phpcs['warnings']} Warnings\n\r" : '';

		// JSON Payload.
		$payload            = new stdClass();
		$payload->commit_id = Options::get( 'commit' );
		$payload->event     = $phpcs['errors'] + $phpcs['warnings'] > 0 ? 'REQUEST_CHANGES' : 'APPROVE';

		self::deletePRComments();

		if ( 'REQUEST_CHANGES' === $payload->event ) {
			$payload->body     = "**phpcs** results:\n\r$errors$warnings";
			$payload->comments = [];

			$stringErrors = '';

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
							$comment['body']     = "$type {$message['message']} ({$message['source']})";
							array_push( $payload->comments, (object) $comment );
							$stringErrors .= "$file:{$message['line']} {$comment['body']}\n\r";
						}
					}
				}
			}

			if ( $phpcs['errors'] + $phpcs['warnings'] >= 100 ) {
				unset( $payload->comments );
				$payload->body .= $stringErrors;
			}

			self::editLongReviews();

			self::submitReview( $payload, $stringErrors );

			// Tell GitHub actions that the action has errors or warnings.
			exit( 1 );
		} else {
			$botMessages = [
				":robot: Destroy! Cough... I mean... Approved!",
				":robot: LGTM! :sunglasses:",
				":robot: wow no warnings nice! :fireworks:",
				":panda_face: The panda approves",
				":golfing: Hole in one! No issues found! ",
			];

			$payload->body = $botMessages[ (int) rand( 0, 4 ) ];

			self::submitReview( $payload );

			// Tell GitHub actions that the action has no errors or warnings.
			exit( 0 );
		}
	}

	static function submitReview( $payload, $stringErrors = 'Failed review submit' ) {
		$payloadJSON   = json_encode( $payload );
		$headersString = self::curlHeaders( [
			'"Content-Type: application/json"',
		] );

		$pRId      = Options::get( 'pr-id' );
		$repoOwner = Options::get( 'repo-owner' );
		$repoName  = Options::get( 'repo-name' );
		$url       = self::URL;

		$command = "curl -s -d '$payloadJSON' $headersString -X POST $url/repos/$repoOwner/$repoName/pulls/$pRId/reviews";

		exec( $command, $execResult );

		$submitResult = json_decode( implode( " ", $execResult ), true );

		if ( isset( $submitResult['message'] ) && 'Server Error' === $submitResult['message'] ) {
			print_r( $stringErrors );
		} else {
			print_r( $payload );
		}

		self::maybeSleep();
	}

	static function editLongReviews() {
		$headersString = self::curlHeaders( [
			'"Content-Type: application/json"',
		] );
		$botUsername   = self::getTokenUsername();
		$pRId          = Options::get( 'pr-id' );
		$repoOwner     = Options::get( 'repo-owner' );
		$repoName      = Options::get( 'repo-name' );
		$url           = self::URL;

		$command = "curl -s $headersString -X GET $url/repos/$repoOwner/$repoName/pulls/$pRId/reviews";

		exec( $command, $execResult );

		$reviews = json_decode( implode( "\n", $execResult ), true );

		$stringLimit = 80;
		foreach ( $reviews as $review ) {
			if ( isset( $review['user']['login'], $review['id'] ) && $botUsername === $review['user']['login'] ) {
				if ( 'CHANGES_REQUESTED' === $review['state'] && strlen( $review['body'] ) >= $stringLimit ) {
					$reviewId   = $review['id'];
					$body       = new stdClass();
					$body->body = 'Check errors below';
					$bodyString = json_encode( $body );
					$command    = "curl -s -d '$bodyString' $headersString -X PUT $url/repos/$repoOwner/$repoName/pulls/$pRId/reviews/$reviewId";

					exec( $command, $execResultReview );

					echo "Edited review $reviewId" . PHP_EOL;
				}
			}
		}
	}
}
