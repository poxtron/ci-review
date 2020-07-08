#!/usr/bin/env bash

# Check required env variables
if [[ -z "$GH_BOT_TOKEN" ]]; then
    printf "[\e[0;31mERROR\e[0m] Secret \`GH_BOT_TOKEN\` is missing. Please add it to this action for proper execution.\n"
    exit 1
fi

#cat $GITHUB_EVENT_PATH
#
#exit 1

# Commented out as we now include a modified version of vip-go-ci in the docker image
#cd "$GITHUB_WORKSPACE"

#current_version="vip-go-ci-$GOCI_VERSION"
#
## getting current version of vip-go-ci
#wget https://github.com/Automattic/vip-go-ci/archive/$GOCI_VERSION.tar.gz
#
#tar -xzvf $GOCI_VERSION.tar.gz
#
#mkdir -p vip-go-ci
#
#mv $current_version/* vip-go-ci
#mv $current_version/.* vip-go-ci
#
#rm -r $current_version $GOCI_VERSION.tar.gz

COMMIT_ID=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.head.sha')
#PR_BODY=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.body)
GITHUB_REPO_NAME=${GITHUB_REPOSITORY##*/}
GITHUB_REPO_OWNER=${GITHUB_REPOSITORY%%/*}

BOT_WORKSPACE="/home/etstaging"

rsync -a "$GITHUB_WORKSPACE/" "$BOT_WORKSPACE"
chown -R etstaging:etstaging "$BOT_WORKSPACE"

cd "$BOT_WORKSPACE"/rules

composer install

cd "$BOT_WORKSPACE"

PHPCS_STANDARD="$BOT_WORKSPACE"/rules
PHPCS_PATH="$BOT_WORKSPACE"/rules/vendor/bin/phpcs

echo "./vip-go-ci/vip-go-ci.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --commit=$COMMIT_ID --token=\$GH_BOT_TOKEN --phpcs-path=$PHPCS_PATH --local-git-repo=$BOT_WORKSPACE/repo --phpcs=true --phpcs-standard=$PHPCS_STANDARD --results-comments-sort=true"

gosu etstaging bash -c "./vip-go-ci/vip-go-ci.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --commit=$COMMIT_ID --token=$GH_BOT_TOKEN --phpcs-path=$PHPCS_PATH --local-git-repo=$BOT_WORKSPACE/repo --phpcs=true --phpcs-standard=$PHPCS_STANDARD --results-comments-sort=true"

# post-generic-pr-support-comments