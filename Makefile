#!/usr/bin/make -f

PROCESSORS_NUM := $(shell getconf _NPROCESSORS_ONLN)
GLOBAL_CONFIG := -d memory_limit=-1

.PHONY: all
all: test

.PHONY: clean
clean:
	git clean -Xfq build

.PHONY: clean-all
clean-all: clean
	rm -rf ./vendor
	rm -rf ./composer.lock

.PHONY: check
check:
	php ${GLOBAL_CONFIG} vendor/bin/phpcs --parallel=${PROCESSORS_NUM}

.PHONY: test
test: clean check
	phpdbg -qrr vendor/bin/phpunit

.PHONY: coverage
coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi
