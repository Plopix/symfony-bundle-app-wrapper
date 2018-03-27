# Symfony Bundle App Wrapper

This script helps you to automate a Symfony application installation around a
bundle.

It is useful when to want to automate an application deployment from a bundle
repository.

It has been develop to boostrap an eZ Platform 2.x application on Platform.sh

> Note: Using Symfony 4 and Flex, this could be even more simplified.
(wanna contribute? your are welcome)

Ex:

```
- YourAwesomeBundle
     - .platform
     - bundle
     - lib
     - tests
         - platform.sh
            - bundles.yaml
            - configs.yaml
            - routes.yaml
```

So the script will require

- `bundles.yaml` to automatically add the bundle in the application
- `configs.yaml` to automatically add the configuration in the application
- `routes.yaml` to automatically add the routes in the application

Then the deploy will looks like

```bash
git clone YourAwesomeBundle.git
composer create-project ezsystems/ezplatform --prefer-dist --no-progress --no-interaction --no-scripts
curl -o tests/platform.sh/wrap.php https://raw.githubusercontent.com/Plopix/symfony-bundle-app-wrapper/master/wrap-bundle.php
WRAP_ROOT_DIR=./ezplatform php tests/platform.sh/wrap.php
cd ezplatform
composer update --lock
```

Open Source Examples:
-

[License](LICENSE)
