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
echo "https://repos.zend.com/zendphp/apk_alpine318/" >> /etc/apk/repositories
wget https://repos.zend.com/zendphp/apk_alpine318/zendphp-alpine-devel.rsa.pub -O /etc/apk/keys/zendphp-alpine-devel.rsa.pub
apk update
export PHP_VER=8.2
zendphpctl repo install
zendphpctl php install $PHP_VER
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
zendphpctl fpm is-installed
```
* Install PHP-FPM for the default version
```
zendphpctl fpm install
```
* Configure PHP-FPM support
  * For now just review the configuration
  * Use the defaults for the lab
  * Use `CTL+X` to save and exit
```
export EDITOR=/usr/bin/nano
zendphpctl fpm config
```
* Start PHP-FPM
  * Substitute the PHP version in place of `PHP_VER_ALPINE`
  * Note that with Alpine you *do not* put a period between the major and minor number
  * Example: PHP 8.2 would be "82"
```
export PHP_VER_ALPINE=82
/usr/sbin/php-fpm"$PHP_VER_ALPINE"zend
```
* Confirm PHP-FPM is running
```
ps
```
## Configure nginx for the application and PHP-FPM
Open or create a file `/etc/nginx/http.d/default.conf`
```
nano /etc/nginx/http.d/default.conf
```
* Paste in the following contents:
```
server {
    listen                  80;
    root                    /var/www/html;
    index                   index.php;
    server_name             _;
    client_max_body_size    32m;
    error_page              500 502 503 504  /50x.html;
    location = /50x.html {
          root              /var/lib/nginx/html;
    }
    location ~ \.php$ {
          fastcgi_pass      127.0.0.1:9000;
          fastcgi_index     index.php;
          include           fastcgi.conf;
    }
}
```
* Restart nginx
```
/usr/sbin/nginx -s reload
```
## Install a test PHP application
* Open a terminal window and change to this directory (e.g. `/home/training`)
* Change to the `/var/www/html` directory
* Create the sample PHP app:
```
nano /var/www/html/test.php
```
* Add these contents and save:
```
<?php
phpinfo();
```
* Test the application from your browser:
  * http://10.10.60.10/
  * or:
  * http://localhost:8888

## Install ZendHQ

