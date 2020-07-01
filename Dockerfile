# ubuntu:latest at 2020-05-12T09:35:28IST
#FROM ubuntu@sha256:3235326357dfb65f1781dbc4df3b834546d8bf914e82cce58e6e6b676e23ce8f
FROM elegantthemes/php:7.4.5

LABEL "com.github.actions.icon"="check-circle"
LABEL "com.github.actions.color"="green"
LABEL "com.github.actions.name"="PHPCS Code Review"
LABEL "com.github.actions.description"="This will run phpcs on PRs"

#RUN echo "tzdata tzdata/Areas select Asia" | debconf-set-selections && \
#echo "tzdata tzdata/Zones/Asia select Kolkata" | debconf-set-selections
# Show and log errors
#RUN set -eux
#
#RUN apt-get update; \
#	apt install software-properties-common -y && \
#	add-apt-repository ppa:ondrej/php && \
#	DEBIAN_FRONTEND=noninteractive apt-get install -y \
#	cowsay \
#	git \
#	gosu \
#	jq \
#	php7.4-cli \
#	php7.4-curl \
#    php7.4-mbstring \
#    php7.4-xml \
#	rsync \
#	sudo \
#	tree \
#	vim \
#	zip \
#	unzip \
#	wget ; \
#	rm -rf /var/lib/apt/lists/*; \
#	# verify that the binary works
#	gosu nobody true; \
#	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
#
RUN apt-get update; \
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

#RUN wget https://raw.githubusercontent.com/Automattic/vip-go-ci/master/tools-init.sh -O tools-init.sh && \
#	bash tools-init.sh && \
#	rm -f tools-init.sh

# Clonning vip-go-ci utils
RUN git clone https://github.com/Automattic/vip-go-ci/ /home/etbot/vip-go-ci

RUN git clone https://github.com/elegantthemes/marketplace-phpcs/ /home/etbot/marketplace-phpcs

COPY entrypoint.sh main.sh run-review.php /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh /usr/local/bin/run-review.php

#ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]