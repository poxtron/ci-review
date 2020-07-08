#!/usr/bin/env php
<?php

echo 'DATA ' . print_r($argv, true);

// get git current branch name
    // git rev-parse --abbrev-ref HEAD
// use diff to get the current modified files
    // git diff --name-only master
// pass phpcs through files
    // copy modified files to phpcs scan directory mantaining the same structure
    // scan the entire directory and get results in json
// filter results for only modified lines

// create review comment
// create line review comments
// delete all old reviews from the token user
// delete all line comments
