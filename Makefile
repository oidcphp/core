#!/usr/bin/make -f

.PHONY: clean clean-all check test analyse coverage

# ---------------------------------------------------------------------

all: test analyse

clean:
	rm -rf ./build

clean-all: clean
	rm -rf ./vendor

check:
	php vendor/bin/phpcs

test: check
	phpdbg -qrr vendor/bin/phpunit

analyse:
	php vendor/bin/phpstan analyse src --level=max

coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi
