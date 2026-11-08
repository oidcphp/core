#!/usr/bin/make -f

PROCESSORS_NUM := $(shell getconf _NPROCESSORS_ONLN)
GLOBAL_CONFIG := -d memory_limit=-1
PHP_HAS_XDEBUG_EXTENSION := $(shell php -r 'echo extension_loaded("xdebug") ? 1 : 0;' 2>/dev/null)
PHP_HAS_PCOV_EXTENSION := $(shell php -r 'echo extension_loaded("pcov") ? 1 : 0;' 2>/dev/null)

# Disable the coverage driver for tools that do not need coverage
ifeq ($(PHP_HAS_PCOV_EXTENSION),1)
PHP_DISABLE_COVERAGE_MODE := -d pcov.enabled=0
else ifeq ($(PHP_HAS_XDEBUG_EXTENSION),1)
PHP_DISABLE_COVERAGE_MODE := -d xdebug.mode=off
else
PHP_DISABLE_COVERAGE_MODE :=
endif

CACHED_PHP_FILES = $(shell git diff --cached --name-only --diff-filter=ACM | grep -e '^src' | grep -e '\.php$$')

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
	@mkdir -p .cache/phpcs
	php ${GLOBAL_CONFIG} ${PHP_DISABLE_COVERAGE_MODE} vendor/bin/phpcs --parallel=${PROCESSORS_NUM} --cache=.cache/phpcs/.phpcs.cache

.PHONY: analyse
analyse:
	php ${GLOBAL_CONFIG} ${PHP_DISABLE_COVERAGE_MODE} vendor/bin/phpstan analyse --memory-limit=-1

.PHONY: fast-stan
fast-stan:
	@if [ -z "${CACHED_PHP_FILES}" ]; then echo "No staged PHP files in src, skip."; else \
		php ${GLOBAL_CONFIG} ${PHP_DISABLE_COVERAGE_MODE} vendor/bin/phpstan analyse --memory-limit=-1 ${CACHED_PHP_FILES}; \
	fi

.PHONY: test
test: clean check analyse
	php -d xdebug.mode=coverage vendor/bin/phpunit

.PHONY: coverage
coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi
