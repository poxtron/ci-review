<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class MiscFilterFilePathTest extends TestCase {
	/**
	 * @covers ::vipgoci_filter_file_path
	 */
	public function testFilterFilePath1() {
		$file_name = 'folder1/file1.txt';

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'file_extensions' => [
						'txt'
					]
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'file_extensions' => [
						'ini'
					]
				]
			)
		);
	}

	/**
	 * @covers ::vipgoci_filter_file_path
	 */
	public function testFilterFilePath2() {
		$file_name = 'folder1/file1.txt';

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'file_extensions' => [
						'txt',
						'ini'
					]
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'file_extensions' => [
						'ini',
						'sys'
					]
				]
			)
		);
	}

	/**
	 * @covers ::vipgoci_filter_file_path
	 */
	public function testFilterFilePath3() {
		$file_name = 'folder1/file1.txt';

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder2',
					]
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder1',
					]
				]
			)
		);
	}

	/**
	 * @covers ::vipgoci_filter_file_path
	 */
	public function testFilterFilePath4() {
		$file_name = 'folder1/file1.txt';

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder2',
					],

					'file_extensions' => [
						'txt',
						'ini'
					]
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder1',
					],

					'file_extensions' => [
						'ini'
					]
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder1',
					],

					'file_extensions' => [
						'txt',
						'ini'
					]
				]
			)
		);
	}

	/**
	 * @covers ::vipgoci_filter_file_path
	 */
	public function testFilterFilePath5() {
		$file_name = 'my/unit-tests/folder1/subfolder/file1.txt';

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'folder200',
						'folder3000',
						'folder4000/folder5000/folder6000',
						'SubFolder' // Note: capital 'F'
					],
				]
			)
		);

		$this->assertTrue(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'unit-tests/folder1/subfolder', // Note: not at root level
					],
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'somefoldertesting/otherfolder/foobar123',
						'somefoldertesting/otherfolder/foobar321',
						'my/unit-tests/folder1/subfolder',
					],
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'my/unit-tests',
					],
				]
			)
		);

		$this->assertFalse(
			vipgoci_filter_file_path(
				$file_name,
				[
					'skip_folders' => [
						'my',
					],
				]
			)
		);
	}

}
