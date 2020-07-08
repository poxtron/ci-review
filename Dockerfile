FROM elegantthemes/php:7.2.13

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

COPY entrypoint.sh run-review.php /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh /usr/local/bin/run-review.php

# copy modified version of vip-go-ci
RUN mkdir -p /home/etstaging/vip-go-ci
ADD vip-go-ci /home/etstaging/vip-go-ci

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]