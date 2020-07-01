#!/usr/bin/env bash

# Check required env variables
if [[ -z "$GH_BOT_TOKEN" ]]; then
    printf "[\e[0;31mERROR\e[0m] Secret \`GH_BOT_TOKEN\` is missing. Please add it to this action for proper execution.\n"
    exit 1
fi

# custom path for files to override default files
#custom_path="$GITHUB_WORKSPACE/.github/inspections/vip-go-ci/"
main_script="/usr/local/bin/main.sh"

#if [[ -d "$custom_path" ]]; then
#    rsync -a "$custom_path" /usr/local/bin/
#fi

bash "$main_script" "$@"
