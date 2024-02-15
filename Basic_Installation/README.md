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
Read through these instructions to get an overview of the installation:
  * [https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm](https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm)
### Install `zendphpctl`
Download the zendphpctl script and its signature from our repository:
```
curl -L https://repos.zend.com/zendphp/zendphpctl -o zendphpctl
curl -L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig
```
Validate the signature:
```
echo "$(cat zendphpctl.sig) zendphpctl" | sha256sum -c
```
If the signature is valid, remove the signature file, set permissions for the script, and move it into the path for the root user:
```
rm zendphpctl.sig
chmod +x zendphpctl
mv ./zendphpctl /usr/sbin
```
### Install PHP using `zendphpctl`
Follow the instructions here in the "Installation using Zendphpctl":
* [https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm](https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm)
* Change `PHP_VER` to the desired PHP version (e.g. `8.3`)
```
export PHP_VER=8.2
echo "https://repos.zend.com/zendphp/apk_alpine318/" >> /etc/apk/repositories
wget https://repos.zend.com/zendphp/apk_alpine318/zendphp-alpine-devel.rsa.pub -O /etc/apk/keys/zendphp-alpine-devel.rsa.pub
apk update
zendphpctl repo install
zendphpctl php install $PHP_VER
```
Test for success:
```
php -v
PHP 8.2.15 (cli) (built: Jan 19 2024 08:29:07) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.15, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.15, Copyright (c), by Zend Technologies
```
Check for installed modules:
```
php -m
```
## Install PHP-FPM
Check if FPM support is installed (fpm is-installed)
```
zendphpctl fpm is-installed
```
Install PHP-FPM for the default version
```
zendphpctl fpm install
```
Configure PHP-FPM support
* For now just review the configuration
* Use the defaults for the lab
* Use `CTL+X` to save and exit
```
export EDITOR=/usr/bin/nano
zendphpctl fpm config
```
Start PHP-FPM
* Substitute the PHP version in place of `PHP_VER_ALPINE`
* Note that with Alpine you *do not* put a period between the major and minor number
* Example: PHP 8.2 would be "82"
```
export PHP_VER_ALPINE=82
/usr/sbin/php-fpm"$PHP_VER_ALPINE"zend
```
Confirm PHP-FPM is running
```
ps
```
## Configure nginx for the application and PHP-FPM
Create a directory for the web server document root:
```
mkdir /var/www/html
```
Open or create a file `/etc/nginx/http.d/default.conf`
```
nano /etc/nginx/http.d/default.conf
```
Overwrite the contents with the following:
* Use `CTL+X` to save and exit
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
Restart nginx
```
/usr/sbin/nginx -s reload
```
## Install a test PHP application
Open a terminal window and change to this directory (e.g. `/home/training`)
* Create the sample PHP app:
* Use `CTL+X` to save and exit
```
nano /var/www/html/test.php
```
Add these contents and save:
```
<?php
phpinfo();
```
Test the application from your browser:
* http://10.10.60.10/test.php
* or:
* http://localhost:8888/test.php

## Install ZendHQ
Review the instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm)
* If not already done, install the Zend repo:
```
zendphpctl repo install
```
### Install the ZendHQ daemon
This is installed on the server running ZendHQ
```
apk add zendhqd
```
Review the `zendhqd` configuration at `/opt/zend/zendphp/etc/zendhqd.ini`
```
nano /opt/zend/zendphp/etc/zendhqd.ini
```
Change the following settings in `zendhqd.ini`:
* `zendhqd.daemon_uri = tcp://0.0.0.0:10090`
* `zendhqd.websocket.interface = 0.0.0.0:10091`
* Use `CTL+X` to save exit

Start the daemon
* NOTE: you will receive a message regarding a missing license, however the daemon will still run
```
/opt/zend/zendphp/bin/zendhqd -D
```
Confirm that the daemon is running
```
ps
```

### Install the ZendHQ PHP Extension
NOTE: the ZendHQ extension needs to be installed on any PHP installation you wish to monitor
* In the case of the lab, install the
ZendHQ PHP extension in the same Docker container
```
zendphpctl ext install zendhq
```
Confirm that extension is installed:
```
php -m
```
Review the configuration. Make changes as desired.
* Use `CTL+X` to save and exit
```
nano /etc/php/82zend/conf.d/10_zendhq.ini
```
Restart PHP-FPM
* NOTE: on Debian/Ubuntu or RHEL/Fedora/CentOS systems PHP-FPM will be running under a run service
* For Alpine Linux you need to kill the master process and restart it
* Assign the correct PHP version number to `PHP_VER`
  * For Alpine Linux the version numbering does not have a period (".")
```
export PHP_VER_ALPINE=82
```
Find the process ID (`PID`) for the PHP-FPM master process:
```
ps |grep php-fpm
```
Kill the master process
* Substitute the PID number in place of "PID":
```
kill PID
```
Restart PHP-FPM
```
/usr/sbin/php-fpm"$PHP_VER_ALPINE"zend
```
Confirm the process is running
```
ps |grep php-fpm
```

## Install the ZendHQ GUI
The ZendHQ user interface runs separately from the web server
* Install the GUI on your local computer (*not* inside the container!)
Review the GUI instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
* The downloads are located here:
  * [https://downloads.zend.com/zendphp/zendhq-ui/](https://downloads.zend.com/zendphp/zendhq-ui/)
Grab the appropriate compressed download file
```
# e.g. for Linux:
wget https://downloads.zend.com/zendphp/zendhq-ui/zendhq-ui-linux-release.tar.gz
```
Extract them using your preferred archiving tool
```
# e.g. for Linux:
tar -xvf ./zendhq-ui-linux-release.tar.gz
```
Run the binary
* NOTE: there are different binaries for different CPU architectures
```
# e.g. for Linux on an Intel _x86 CPU:
./zend-hq-linux_x64
```
For the initial login, enter the username `admin` and the use `zendphp` for the token
