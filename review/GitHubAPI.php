<?php

namespace ET\PR_Review;

use stdClass;

class GitHubAPI {

	const URL = 'https://api.github.com';

	const SLEEP = true;

	static $userName = '';

	/**
	 *
	 * @param bool $remote Whether to retrieve the diff using the api or git command.
	 *
	 * @return array Array containing each line of the git diff.
	 */
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

	/**
	 * @param array $extra Array with extra headers to add.
	 *
	 * @return string Curl headers string.
	 */
	static function curlHeaders( $extra = [] ) {
		$token   = Options::get( 'token' );
		$headers = array_merge( $extra, [
			"\"Authorization: Bearer $token\"",
		] );

		return '-H ' . implode( ' -H ', $headers );
	}

	/**
	 * Wait a little bit on each github api call so that it doesn't saturate.
	 */
	static function maybeSleep() {
		if ( self::SLEEP ) {
			sleep( 2 );
		}
	}

	/**
	 * Main function, gets all errors and warnings from phpcs and eslint and submits the PR review.
	 */
	static function createReview() {
		$phpcs         = RunPhpcs::getResults();
		$phpcsErrors   = $phpcs['errors'] > 0 ? ":no_entry_sign: {$phpcs['errors']} Errors\n\r" : '';
		$phpcsWarnings = $phpcs['warnings'] > 0 ? ":warning: {$phpcs['warnings']} Warnings\n\r" : '';

		if ( do_eslint() ) {
			$eslint              = RunESLint::getResults();
			$eslintErrors        = $eslint['errors'] > 0 ? ":no_entry_sign: {$eslint['errors']} Errors\n\r" : '';
			$eslintWarnings      = $eslint['warnings'] > 0 ? ":warning: {$eslint['warnings']} Warnings\n\r" : '';
			$totalErrorsWarnings = $phpcs['errors'] + $phpcs['warnings'] + $eslint['errors'] + $eslint['warnings'];
		} else {
			$totalErrorsWarnings = $phpcs['errors'] + $phpcs['warnings'];
		}

		// JSON Payload.
		$payload            = new stdClass();
		$payload->commit_id = Options::get( 'commit' );
		$payload->event     = $totalErrorsWarnings > 0 ? 'REQUEST_CHANGES' : 'APPROVE';

		self::deletePRComments();

		if ( 'REQUEST_CHANGES' === $payload->event ) {
			$payload->body = $phpcs['errors'] + $phpcs['warnings'] > 0 ? "**phpcs** results:\n\r$phpcsErrors$phpcsWarnings" : '';

			if ( do_eslint() ) {
				/** @noinspection PhpUndefinedVariableInspection */
				$payload->body .= $eslint['errors'] + $eslint['warnings'] > 0 ? "**eslint** results:\n\r$eslintErrors$eslintWarnings" : '';
			}

			$payload->comments = [];

			$stringErrors = '';

			$diffResults = PrepareFiles::getDiffResults();

			if( do_eslint() ){
				/** @noinspection PhpUndefinedVariableInspection */
				$results = array_merge( $phpcs['results'], $eslint['results'] );
			} else {
				$results = $phpcs['results'];
			}

			foreach ( $results as $file => $messages ) {
				if ( isset( $diffResults[ $file ] ) ) {
					foreach ( $messages as $message ) {
						$line = "+{$message['line']}";
						$type = 'WARNING' === $message['type'] ? ':warning:' : ':no_entry_sign:';
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

			// Group errors that are on the same line.
			foreach ( $payload->comments as $key => $comment ) {
				if ( ! isset( $payload->comments[ $key ] ) ) {
					continue;
				}
				$comments2 = $payload->comments;
				unset( $comments2[ $key ] );
				foreach ( $comments2 as $key2 => $comment2 ) {
					if ( $comment2->position === $comment->position ) {
						$payload->comments[ $key ]->body .= "\n\r" . $comment2->body;
						unset( $payload->comments[ $key2 ] );
					}
				}
			}

			$payload->comments = array_values( $payload->comments );

			if ( count( $payload->comments ) >= 100 ) {
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

			$payload->body = $botMessages[ (int) rand( 0, count( $botMessages ) - 1 ) ];

			self::submitReview( $payload );

			// Tell GitHub actions that the action has no errors or warnings.
			exit( 0 );
		}
	}

	/**
	 * Removes previous PR comments so that the comment sections doesn't saturate.
	 */
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

	/**
	 * @return string Gets GitHub username associated with the given access token.
	 */
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

	/**
	 * If the errors where added on the review body on an earlier review it will delete them so that comments are not saturated.
	 */
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

		foreach ( $reviews as $review ) {
			if ( isset( $review['user']['login'], $review['id'] ) && $botUsername === $review['user']['login'] ) {
				$reviewId = $review['id'];
				$body     = new stdClass();
				if ( 'CHANGES_REQUESTED' === $review['state'] ) {
					$body->body = ':arrow_double_down:';
				} elseif ( 'APPROVED' === $review['state'] ) {
					$body->body = ':arrow_double_down:';
				}

				if ( in_array( $review['state'], [ 'CHANGES_REQUESTED', 'APPROVED' ] ) ) {
					echo "Edited review $reviewId" . PHP_EOL;
					$bodyString = json_encode( $body );
					$command    = "curl -s -d '$bodyString' $headersString -X PUT $url/repos/$repoOwner/$repoName/pulls/$pRId/reviews/$reviewId";

					exec( $command, $execResultReview );
				}
			}
		}
	}

	/**
	 * @param object $payload      The object to be sent to GitHub API.
	 * @param string $stringErrors All of the warnings and errors on the review, if the POST to GitHub API fails it will print them on the action.
	 */
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
			echo $submitResult['message'] . PHP_EOL;
			print_r( $stringErrors );
			exit( 1 );
		} else {
			print_r( $payload );
		}

		self::maybeSleep();
	}
}
