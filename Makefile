### ——————————————————————————————————————————————————————————————————
### —— Local Makefile
### ——————————————————————————————————————————————————————————————————

# Register Toolkit as Symfony Container
SF_CONTAINERS += openapi

include vendor/splash/toolkit/make/toolkit.mk
include vendor/badpixxel/php-sdk/make/sdk.mk

.PHONY: upgrade
upgrade:
	composer update

.PHONY: verify
verify:	# Verify Code
	php vendor/bin/grumphp run --testsuite=travis
	php vendor/bin/grumphp run --testsuite=csfixer
	php vendor/bin/grumphp run --testsuite=phpstan

.PHONY: phpstan
phpstan:	# Execute Php Stan
	php vendor/bin/grumphp run --testsuite=phpstan

.PHONY: test
test: 	## Execute Functional Test in All Containers
	$(MAKE) up
	$(DOCKER_COMPOSE) exec toolkit php vendor/bin/phpunit
