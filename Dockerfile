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
	git \
    && curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && apt-install nodejs

RUN useradd -m -s /bin/bash etstaging

COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

# copy review script
RUN mkdir -p /home/etstaging/review
ADD review /home/etstaging/review

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]