#!/usr/bin/env bash

# Check required env variables
if [[ -z "$GH_BOT_TOKEN" ]]; then
    printf "[\e[0;31mERROR\e[0m] Secret \`GH_BOT_TOKEN\` is missing. Please add it to this action for proper execution.\n"
    exit 1
fi

# shellcheck disable=SC2164
cd "$GITHUB_WORKSPACE"

current_version="vip-go-ci-$GOCI_VERSION"

# getting current version of vip-go-ci
wget https://github.com/Automattic/vip-go-ci/archive/$GOCI_VERSION.tar.gz

tar -xzvf ${current_version}.tar.gz

mkdir -p vip-go-ci

mv $current_version/* vip-go-ci

ls -lR

exit 1;

cd "$GITHUB_WORKSPACE"/rules || printf "[\e[0;31mERROR\e[0m]\n"

composer install

mkdir -p "$GITHUB_WORKSPACE"/vip-go-ci

#
#
#
#COMMIT_ID=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.head.sha')
#PR_BODY=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.body)
#GITHUB_REPO_NAME=${GITHUB_REPOSITORY##*/}
#GITHUB_REPO_OWNER=${GITHUB_REPOSITORY%%/*}
#chown -R etbot:etbot /home/etbot/
#gosu etbot bash -c "cd /home/etbot/marketplace-phpcs && composer install"
#gosu etbot bash -c "/usr/local/bin/run-review.php $COMMIT_ID $PR_BODY $GITHUB_REPO_NAME $GITHUB_REPO_OWNER $GITHUB_EVENT_PATH $GITHUB_REPOSITORY $GH_BOT_TOKEN"

# custom path for files to override default files
#custom_path="$GITHUB_WORKSPACE/.github/inspections/vip-go-ci/"
#main_script="/usr/local/bin/main.sh"

#if [[ -d "$custom_path" ]]; then
#    rsync -a "$custom_path" /usr/local/bin/
#fi

#bash "$main_script" "$@"
