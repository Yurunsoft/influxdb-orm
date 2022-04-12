ARG SWOOLE_DOCKER_VERSION
FROM phpswoole/swoole:${SWOOLE_DOCKER_VERSION}

RUN apt update

RUN apt install unzip
