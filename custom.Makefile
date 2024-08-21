# custom.Makefile
# Be sure to set your IDE to "tab" indentation and not "space"

## Less typing to get a non-permission-ruining nginx user terminal
.PHONY: drupbash
drupbash:
	docker compose exec --user nginx drupal bash

ksul_update:
## Performs SQL backup first. Sets site in maintenance and performs composer and db updates then unsets maintenance mode
	$(MAKE) access_open
	docker compose exec --user nginx drupal drush mset 1 -y
	$(MAKE) drupal-database-dump DEST=prodmySQLbak.sql
	docker compose exec --user nginx drupal composer install
	docker compose exec --user nginx drupal drush updb -y
	docker compose exec --user nginx drupal drush cim -y
	docker compose exec --user nginx drupal drush cr
	docker compose exec --user nginx drupal drush mset 0 -y
	$(MAKE) access_secure
	$(MAKE) site_check

access_open:
## Will provide access to the nginx group. DO NOT USE STANDALONE unless you use "make access_secure" immediately after performing your commands
	$(MAKE) set-files-owner SRC=codebase/
	docker compose exec drupal chmod -R g+w /var/www/drupal/web/ /var/www/drupal/vendor/ /var/www/drupal/composer.json /var/www/drupal/composer.lock /var/www/drupal/config/

access_secure:
	$(MAKE) set-files-owner SRC=codebase/
	docker compose exec drupal chmod -R g-w /var/www/drupal/web/

site_check:
	./ksul_system_check.sh