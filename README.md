# Symfony Bundle App Wrapper

This script helps you to automate a Symfony application installation around a
bundle.

It is useful when to want to automate an application deployment from a bundle
repository.

It has been develop to boostrap an eZ Platform 2+ application locally and on Platform.sh 

So the script will require

- `bundles.yaml` to automatically add the bundle in the application
- `configs.yaml` to automatically add the configuration in the application
- `routes.yaml` to automatically add the routes in the application


The folder structure would be:

```
- YourAwesomeBundle
     - .platform
     - bundle
     - lib
     - tests
         - provisioning
            - bundles.yaml
            - configs.yaml
            - routes.yaml
```

## Using Makefile (locally)

```
.PHONY: installez
installez: ## Install eZ as the local project
	@docker run -d -p 3366:3306 --name ezdbbundlecontainer -e MYSQL_ROOT_PASSWORD=ezplatform mariadb:10.3
	@composer create-project ezsystems/ezplatform --prefer-dist --no-progress --no-interaction --no-scripts ezplatform
	@curl -o tests/provisioning/wrap.php https://raw.githubusercontent.com/Plopix/symfony-bundle-app-wrapper/master/wrap-bundle.php
	@WRAP_APP_DIR=./ezplatform WRAP_BUNDLE_DIR=./ php tests/provisioning/wrap.php
	@rm tests/provisioning/wrap.php
	@echo "Please set up this way:"
	@echo "\tenv(DATABASE_HOST)     -> 127.0.0.1"
	@echo "\tenv(DATABASE_PORT)     -> 3366"
	@echo "\tenv(DATABASE_PASSWORD) -> ezplatform"
	@cd $(EZ_DIR) && COMPOSER_MEMORY_LIMIT=-1 composer update --lock
	@cd $(EZ_DIR) && bin/console ezplatform:install clean
	@cd $(EZ_DIR) && bin/console cache:clear
```


## Using Platform.sh

Then the deploy will looks like

```bash
git clone YourAwesomeBundle.git
composer create-project ezsystems/ezplatform --prefer-dist --no-progress --no-interaction --no-scripts
curl -o tests/provisioning/wrap.php https://raw.githubusercontent.com/Plopix/symfony-bundle-app-wrapper/master/wrap-bundle.php
WRAP_ROOT_DIR=./ezplatform php tests/provisioning/wrap.php
cd ezplatform
composer update --lock
```

[License](LICENSE)
