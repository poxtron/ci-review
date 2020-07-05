FROM elegantthemes/php:7.4.5

LABEL "com.github.actions.icon"="check-circle"
LABEL "com.github.actions.color"="orange"
LABEL "com.github.actions.name"="PHPCS Code Review"
LABEL "com.github.actions.description"="This will run phpcs on PRs"

# Show and log errors
RUN set -eux; \
	apt-get update; \
	DEBIAN_FRONTEND=noninteractive apt-get install -y \
	unzip \
	jq \
	rsync \
	tree \
	vim \
	zip \
	wget \
	git;

RUN useradd -m -s /bin/bash etstaging

COPY entrypoint.sh main.sh run-review.php /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh /usr/local/bin/run-review.php

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Docker container run command as it is run on github.
#/usr/bin/docker run
#--name poxtroncireviewlatest_f55b90
#--label 3888d3
#--workdir /github/workspace
#--rm
#-e GH_BOT_TOKEN
#-e HOME
#-e GITHUB_JOB
#-e GITHUB_REF
#-e GITHUB_SHA
#-e GITHUB_REPOSITORY
#-e GITHUB_REPOSITORY_OWNER
#-e GITHUB_RUN_ID
#-e GITHUB_RUN_NUMBER
#-e GITHUB_ACTOR
#-e GITHUB_WORKFLOW
#-e GITHUB_HEAD_REF
#-e GITHUB_BASE_REF
#-e GITHUB_EVENT_NAME
#-e GITHUB_SERVER_URL
#-e GITHUB_API_URL
#-e GITHUB_GRAPHQL_URL
#-e GITHUB_WORKSPACE
#-e GITHUB_ACTION
#-e GITHUB_EVENT_PATH
#-e RUNNER_OS
#-e RUNNER_TOOL_CACHE
#-e RUNNER_TEMP
#-e RUNNER_WORKSPACE
#-e ACTIONS_RUNTIME_URL
#-e ACTIONS_RUNTIME_TOKEN
#-e ACTIONS_CACHE_URL
#-e GITHUB_ACTIONS=true
#-e CI=true -v "/var/run/docker.sock":"/var/run/docker.sock"
#-v "/home/runner/work/_temp/_github_home":"/github/home"
#-v "/home/runner/work/_temp/_github_workflow":"/github/workflow"
#-v "/home/runner/work/citest/citest":"/github/workspace" poxtron/ci-review:latest