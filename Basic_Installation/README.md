# Basic Installation Labs

Use this set of containers to practice installing ZendPHP and ZendHQ

## Build the Images
* Make sure you have Docker Desktop or Docker CE + Docker Compose installed (see `/path/to/repo/README.md`)
* Open a terminal window and change to this directory (e.g. `/path/to/repo/Basic_Installation`)
* Build the images:
```
docker-compose build
```
* Run the images (use the `-d` option to run in background)
```
docker-compose up -d
```

## Install ZendPHP
Follow these instructions to install ZendPHP from a terminal window in this directory (e.g. `/path/to/repo/Basic_Installation`)
* Open a shell into the ZendPHP container:
```
docker exec -it zendphp /bin/bash
```
* Read through these instructions to get an overview of the installation:
  * [https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm](https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm)
* Install `zendphpctl`
    * Download the zendphpctl script and its signature from our repository:
```
curl -L https://repos.zend.com/zendphp/zendphpctl -o zendphpctl
curl -L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig
```
    * Validate the signature:
```
echo "$(cat zendphpctl.sig) zendphpctl" | sha256sum -c
```
    * If the signature is valid, remove the signature file, set permissions for the script, and move it into the path for the root user:
```
rm zendphpctl.sig
chmod +x zendphpctl
mv ./zendphpctl /usr/sbin
```
* Follow the instructions here in the "Installation using Zendphpctl":
  * [https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm](https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm)
  * Change `PHP_VER` to the desired PHP version (e.g. `8.3`)
```
# echo "https://repos.zend.com/zendphp/apk_alpine318/" >> /etc/apk/repositories
# wget https://repos.zend.com/zendphp/apk_alpine318/zendphp-alpine-devel.rsa.pub -O /etc/apk/keys/zendphp-alpine-devel.rsa.pub
# apk update
# export PHP_VER=8.2
# zendphpctl repo install
# zendphpctl php install $PHP_VER
```
  * Test for success:
```
# php -v
PHP 8.2.15 (cli) (built: Jan 19 2024 08:29:07) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.15, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.15, Copyright (c), by Zend Technologies
```
  * Check for installed modules:
```
php -m
```
## Install PHP-FPM
* Check if FPM support is installed (fpm is-installed)
```
# zendphpctl fpm is-installed
Checking if FPM is installed for PHP 8.2...
FPM IS NOT installed for version 8.2
```
* Install PHP-FPM for the default version
```
# zendphpctl fpm install
Installing FPM for PHP version 8.2
...
```
* Configure PHP-FPM support
```
# export EDITOR=/usr/bin/nano
# zendphpctl fpm config
Using PHP version 8.2
```
* Start PHP-FPM
  * Substitute the PHP version in place of `PHP_VER_ALPINE`
  * Note that with Alpine you *do not* put a period between the major and minor number
  * Example: PHP 8.2 would be "82"
```
export PHP_VER_ALPINE=82
/usr/sbin/php-fpm82$PHP_VER_ALPINE
```
