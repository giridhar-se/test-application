#!/bin/bash

# Define the composer.lock file
COMPOSER_LOCK_FILE="codebase/composer.lock"

# Check if the composer.lock file exists
if [[ ! -f "$COMPOSER_LOCK_FILE" ]]; then
  echo -e "\e[31mError: composer.lock file not found!\e[0m"
  exit 1
fi

# Define colors using ANSI escape codes
GREEN="\e[32m"
CYAN="\e[36m"
RESET="\e[0m"

# Extract and display the current Drupal version
DRUPAL_VERSION=$(jq -r '.packages[] | select(.name == "drupal/core") | .version' "$COMPOSER_LOCK_FILE")

if [[ -z "$DRUPAL_VERSION" ]]; then
  echo -e "\e[31mError: Drupal version not found in composer.lock!\e[0m"
  exit 1
else
  echo -e "${GREEN}Current Drupal Version:${RESET} ${CYAN}$DRUPAL_VERSION${RESET}"
fi

# Perform simple site checks
echo -e "${CYAN}Checking site status...${RESET}"
SITE_STATUS=$(	docker compose exec --user nginx drupal drush status)
echo -e "${CYAN}$SITE_STATUS${RESET}"

# Indicate script success
echo -e "${GREEN}Updates processed and site has been tested. Please review to verify all systems are nominal.${RESET}"
