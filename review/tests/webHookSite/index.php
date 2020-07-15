<?php
/**
 * This will store all webhoo
 */

if ( ! empty( $_SERVER['HTTP_X_GITHUB_EVENT'] ) ) {
	$data     = array_merge( $_SERVER, $_POST, $_GET );
	$fileName = time() . "-" . "{$_SERVER['HTTP_X_GITHUB_EVENT']}.json";
	$payload  = json_encode( json_decode( $data['payload'] ), JSON_PRETTY_PRINT );
	file_put_contents( $fileName, print_r( $payload, true ) );
}

echo '<h2>There is nothing to see here kitty</h2>';