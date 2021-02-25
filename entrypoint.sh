#!/usr/bin/env bash

# Check required env variables
if [[ -z "$GH_BOT_TOKEN" ]]; then
    printf "[\e[0;31mERROR\e[0m] Secret \`GH_BOT_TOKEN\` is missing. Please add it to this action for proper execution.\n"
    exit 1
fi

BODY=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.body')

echo "$BODY"

if [[ "[do_eslint]" == *"$BODY"* ]]; then
	DO_ESLINT='true'
else
	DO_ESLINT='false'
fi

COMMIT_ID=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.head.sha')
PR_ID=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.number)
BASE_BRANCH=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.base.ref)

GITHUB_REPO_NAME=${GITHUB_REPOSITORY##*/}
GITHUB_REPO_OWNER=${GITHUB_REPOSITORY%%/*}

BOT_WORKSPACE="/home/etstaging"

PHPCS_STANDARD="$BOT_WORKSPACE"/rules
PHPCS_PATH="$BOT_WORKSPACE"/rules/vendor/bin/phpcs

rsync -a "$GITHUB_WORKSPACE/" "$BOT_WORKSPACE"
chown -R etstaging:etstaging "$BOT_WORKSPACE"

cd "$BOT_WORKSPACE"/rules

gosu etstaging bash -c "composer install -q"

if [ "$DO_ESLINT" = true ]; then
	cd "$BOT_WORKSPACE"/review/eslint
	gosu etstaging bash -c "yarn install -s"
fi

cd "$BOT_WORKSPACE"

gosu etstaging bash -c "./review/review.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --repo-path=$BOT_WORKSPACE/repo --token=$GH_BOT_TOKEN --base-branch=$BASE_BRANCH --pr-id=$PR_ID --phpcs-path=$PHPCS_PATH --phpcs-standard=$PHPCS_STANDARD --commit=$COMMIT_ID"
