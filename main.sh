#!/usr/bin/env bash

#cd $GITHUB_WORKSPACE || exit 0
#
COMMIT_ID=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.head.sha')
#
#echo "COMMIT ID: $COMMIT_ID"
#
PR_BODY=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.body)
GITHUB_REPO_NAME=${GITHUB_REPOSITORY##*/}
GITHUB_REPO_OWNER=${GITHUB_REPOSITORY%%/*}

chown -R etbot:etbot /home/etbot/
gosu etbot bash -c "cd /home/etbot/marketplace-phpcs && composer install"

#gosu etbot bash -c "/usr/local/bin/run-review.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --commit=$COMMIT_ID --token=$GH_BOT_TOKEN"
gosu etbot bash -c "/usr/local/bin/run-review.php $COMMIT_ID $PR_BODY $GITHUB_REPO_NAME $GITHUB_REPO_OWNER $GITHUB_EVENT_PATH $GITHUB_REPOSITORY"

#if [[ "$PR_BODY" == *"[do-not-scan]"* ]]; then
#  echo "[do-not-scan] found in PR description. Skipping PHPCS scan."
#  exit 0
#fi
## shellcheck disable=SC2034
#stars=$(printf "%-30s" "*")
#
#export ETBOT_WORKSPACE="/home/etbot/github-workspace"
## shellcheck disable=SC2034
#hosts_file="$GITHUB_WORKSPACE/.github/hosts.yml"
#
## Delete all the folders to be skipped to ignore them from being scanned.
#if [[ -n "$SKIP_FOLDERS" ]]; then
#
#  folders=(${SKIP_FOLDERS//,/ })
#
#  for folder in ${folders[@]}; do
#    path_of_folder="$GITHUB_WORKSPACE/$folder"
#    [[ -d "$path_of_folder" ]] && rm -rf $path_of_folder
#  done
#fi
#
#rsync -a "$GITHUB_WORKSPACE/" "$ETBOT_WORKSPACE"
#rsync -a /root/vip-go-ci-tools/ /home/etbot/vip-go-ci-tools
#chown -R etbot:etbot /home/etbot/
#
