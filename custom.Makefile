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

# Usage:
# make revert              -> prompts for both number of commits and revert type
# make revert n=3          -> skips number prompt, still asks for revert type
# make revert n=3 type=hard -> skips all prompts (both number and type)
revert:
	@if [ -z "$(n)" ]; then \
		read -p "How many commits to revert? " n; \
	fi; \
	if [ -z "$(type)" ]; then \
		echo "Choose revert type:"; \
		echo "  1) soft  - keep changes staged"; \
		echo "  2) mixed - keep changes unstaged"; \
		echo "  3) hard  - discard all changes"; \
		read -p "Enter choice (1-3): " choice; \
		case $$choice in \
			1) type=soft ;; \
			2) type=mixed ;; \
			3) type=hard ;; \
			*) echo "Invalid choice. Defaulting to mixed."; type=mixed ;; \
		esac; \
	fi; \
	echo "About to perform a '$${type}' reset $${n} commit(s) back."; \
	read -p "Are you sure? [y/N]: " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		echo "Reverting $${n} commit(s) back with '$${type}' reset..."; \
		git reset --$${type} HEAD~$${n}; \
		echo "Successfully reverted $${n} commit(s) with '$${type}' reset."; \
	else \
		echo "Operation cancelled."; \
	fi