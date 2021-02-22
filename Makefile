#!/usr/bin/make -f

.PHONY: clean clean-all check test coverage

# ---------------------------------------------------------------------

all: test

clean:
	git clean -Xfq build

clean-all: clean
	rm -rf ./vendor
	rm -rf ./composer.lock

check:
	php vendor/bin/phpcs

test: clean check
	phpdbg -qrr vendor/bin/phpunit

coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi
