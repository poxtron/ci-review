#!/usr/bin/env bash

cd $GITHUB_WORKSPACE

COMMIT_ID=$(cat $GITHUB_EVENT_PATH | jq -r '.pull_request.head.sha')

echo "COMMIT ID: $COMMIT_ID"

PR_BODY=$(cat "$GITHUB_EVENT_PATH" | jq -r .pull_request.body)
if [[ "$PR_BODY" == *"[do-not-scan]"* ]]; then
  echo "[do-not-scan] found in PR description. Skipping PHPCS scan."
  exit 0
fi

stars=$(printf "%-30s" "*")

export RTBOT_WORKSPACE="/home/rtbot/github-workspace"
hosts_file="$GITHUB_WORKSPACE/.github/hosts.yml"

# Delete all the folders to be skipped to ignore them from being scanned.
if [[ -n "$SKIP_FOLDERS" ]]; then

  folders=(${SKIP_FOLDERS//,/ })

  for folder in ${folders[@]}; do
    path_of_folder="$GITHUB_WORKSPACE/$folder"
    [[ -d "$path_of_folder" ]] && rm -rf $path_of_folder
  done
fi

rsync -a "$GITHUB_WORKSPACE/" "$RTBOT_WORKSPACE"
rsync -a /root/vip-go-ci-tools/ /home/rtbot/vip-go-ci-tools
chown -R rtbot:rtbot /home/rtbot/

GITHUB_REPO_NAME=${GITHUB_REPOSITORY##*/}
GITHUB_REPO_OWNER=${GITHUB_REPOSITORY%%/*}

if [[ -n "$VAULT_GITHUB_TOKEN" ]] || [[ -n "$VAULT_TOKEN" ]]; then
  export GH_BOT_TOKEN=$(vault read -field=token secret/rtBot-token)
fi

phpcs_standard=''

defaultFiles=(
  '.phpcs.xml'
  'phpcs.xml'
  '.phpcs.xml.dist'
  'phpcs.xml.dist'
)

phpcsfilefound=1

for phpcsfile in "${defaultFiles[@]}"; do
  if [[ -f "$RTBOT_WORKSPACE/$phpcsfile" ]]; then
      phpcs_standard="--phpcs-standard=$RTBOT_WORKSPACE/$phpcsfile"
      phpcsfilefound=0
  fi
done

if [[ $phpcsfilefound -ne 0 ]]; then
    if [[ -n "$1" ]]; then
      phpcs_standard="--phpcs-standard=$1"
    else
      phpcs_standard="--phpcs-standard=WordPress"
    fi
fi

[[ -z "$SKIP_FOLDERS" ]] && skip_folders_option='' || skip_folders_option="--skip-folders='$SKIP_FOLDERS'"

/usr/games/cowsay "Hello world"

/usr/games/cowsay "Running with the flag $phpcs_standard"

echo "Running the following command"
echo "/home/rtbot/vip-go-ci-tools/vip-go-ci/vip-go-ci.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --commit=$COMMIT_ID --token=\$GH_BOT_TOKEN --phpcs-path=/home/rtbot/vip-go-ci-tools/phpcs/bin/phpcs --local-git-repo=/home/rtbot/github-workspace --phpcs=true $phpcs_standard $skip_folders_option --lint=true"

gosu rtbot bash -c "/home/rtbot/vip-go-ci-tools/vip-go-ci/vip-go-ci.php --repo-owner=$GITHUB_REPO_OWNER --repo-name=$GITHUB_REPO_NAME --commit=$COMMIT_ID --token=$GH_BOT_TOKEN --phpcs-path=/home/rtbot/vip-go-ci-tools/phpcs/bin/phpcs --local-git-repo=/home/rtbot/github-workspace --phpcs=true $phpcs_standard $skip_folders_option --lint=true"
