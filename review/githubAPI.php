<?php

namespace ET\PR_Review;

class githubAPI {

	const URL = 'https://api.github.com';

	// TODO change below to true on production.
	const SLEEP = false;

	static function getDiff( $remote = true ) {
		// create temporary file to store diff from local or remote source
		$tempfile = tempnam( sys_get_temp_dir(), '' );

		if ( $remote ) {
			$pRId      = Options::get( 'pr-id' );
			$repoOwner = Options::get( 'repo-owner' );
			$repoName  = Options::get( 'repo-name' );

			$url = githubAPI::URL;

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
		$headers = $extra + [
				"\"Authorization: Bearer $token\""
			];

		return '-H ' . implode( ' -H ', $headers );
	}

	static function maybeSleep() {
		if ( self::SLEEP ) {
			sleep( 10 );
		}
	}
}