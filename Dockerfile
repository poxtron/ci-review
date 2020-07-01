FROM elegantthemes/php:7.4.5

LABEL "com.github.actions.icon"="check-circle"
LABEL "com.github.actions.color"="green"
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

RUN useradd -m -s /bin/bash etbot

# TODO maybe create modified one just to make sure we have the latest version
#RUN wget https://raw.githubusercontent.com/Automattic/vip-go-ci/master/tools-init.sh -O tools-init.sh && \
#	bash tools-init.sh && \
#	rm -f tools-init.sh

# TODO maybe checkout this repos using github action :thinking:
# Clonning vip-go-ci utils
RUN git clone https://github.com/Automattic/vip-go-ci/ /home/etbot/vip-go-ci

RUN git clone https://github.com/elegantthemes/marketplace-phpcs/ /home/etbot/marketplace-phpcs

COPY entrypoint.sh main.sh run-review.php /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh /usr/local/bin/run-review.php

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]