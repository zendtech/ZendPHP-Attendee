# Demo Installation

Use this container to practice installing ZendPHP and ZendHQ
* Container is based on Ubuntu Linux
Prerequisites:
* A version of Linux
  * You can build the Docker image specified here
  * If running Windows you can use WSL
  * If running Mac you can just open a terminal window and proceed from there
  * Make sure you have the `curl`,`nano` and `net-tools` packages installed
* nginx
  * For more information: [https://nginx.org/en/docs/install.html](https://nginx.org/en/docs/install.html)
* This repository
  * You can either clone it or download and unzip
## Build the Image
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
NOTE that these commands assume you are the root user.
* This is indicated by the command prompt `#`.
* Do not enter `#` when you type in these commands
* Anything listed here that's not preceded by `#` is output
* Do not enter the output as a command!
* If you are not logged in as root, precede each of these commands with `sudo`
Download the zendphpctl script and its signature from the ZendPHP repository:
```
# curl -L https://repos.zend.com/zendphp/zendphpctl -o zendphpctl
# curl -L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig
```
Validate the signature:
```
# echo "$(cat zendphpctl.sig) zendphpctl" | sha256sum -c
```
Check to see what's in your path:
```
echo $PATH
```
If the signature is valid, remove the signature file, set permissions for the script, and move it into a directory in the path for the root user:
```
# rm zendphpctl.sig
# chmod +x zendphpctl
# mv ./zendphpctl /usr/sbin
```
### Install PHP using `zendphpctl`
Follow the instructions here in the "Installation using Zendphpctl":
* [https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm](https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm)
* Change `PHP_VER` to the desired PHP version (e.g. `8.2`)
Install the sources repository
```
# zendphpctl repo install
```
Install target version of PHP
* NOTE: you'll be prompted for timezone information
```
# export PHP_VER=8.2
# zendphpctl php install $PHP_VER
```
Test for success:
```
# php -v
PHP 8.2.16 (cli) (built: Feb 16 2024 13:42:44) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.16, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.16, Copyright (c), by Zend Technologies
```
## Install the cURL extension
To run the demo apps, you need the cURL extension
```
# zendphpctl ext install curl
```
Check for installed modules:
```
# php -m |grep curl
```

## Install PHP-FPM
If you're not running the lab in a container provisioned as part of this course, you need to install `nginx`
* For more information: [https://nginx.org/en/docs/install.html](https://nginx.org/en/docs/install.html)

Check if FPM support is installed (fpm is-installed)
```
# zendphpctl fpm is-installed
```
Install PHP-FPM for the default version
* Be sure to note the location of the PHP-FPM config file
* We'll refer to this file later as `FPM_CONFIG_FILE`
```
# zendphpctl fpm install
```
Configure PHP-FPM support
* For now just review the configuration
* Use the defaults for the lab
* Use `CTL+X` to exit
```
# export EDITOR=/usr/bin/nano
# zendphpctl fpm config
```
Confirm that PHP-FPM is set to "listen" as `www-data` (same as `nginx`):
* Change the settings as needed
* **IMPORTANT**: note the "socket" on which PHP-FPM is listening
  * Later in this tutorial we'll refer to this setting as `<SOCKET>`
  * e.g. `listen = /run/php/php8.2-zend-fpm.sock`
  * The setting for `<SOCKET>` in this example is `/run/php/php8.2-zend-fpm.sock`
Find the user in the PHP-FPM `pool.d` directory
```
# cat /etc/php/8.2-zend/fpm/pool.d/www.conf |grep user
```
Find the location of the socket in the PHP-FPM `pool.d` directory
```
# cat /etc/php/8.2-zend/fpm/pool.d/www.conf |grep listen
```
Start PHP-FPM
* `PHP_VER` was set in an earlier instruction
```
# /etc/init.d/php"$PHP_VER"-zend-fpm start
```
Confirm PHP-FPM is running
```
# ps -ax
```
## Configure nginx for the application and PHP-FPM
Save the current `nginx` default
```
# mv /etc/nginx/sites-available/default /etc/nginx/sites-available/default.old
```
Create a new default config:
```
# nano /etc/nginx/sites-available/default
```
Insert the following:
* NOTE: replace `<SOCKET>` with the `pid` setting noted above
  * e.g. `/run/php/php8.2-zend-fpm.sock;`
* Use `CTL+X` to save and exit
```
server {
	listen 80;
	root /var/www/html;
	server_name _;
	index index.html index.html index.php;
	location / {
		try_files $uri $uri/ /index.php$is_args$args;
	}
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:<SOCKET>;
	}
}
```
NOTE: you might need to assign permissions to the PHP-FPM user to access the socket
* Usually this user is `www-data`
Restart nginx
```
# /etc/init.d/nginx restart
```
## Install a test PHP application
Open a terminal window and change to this directory (e.g. `/home/training`)
* Create the sample PHP app:
* Use `CTL+X` to save and exit
```
# nano /var/www/html/test.php
```
Add these contents and save:
```
<?php
phpinfo();
```
Make sure `nginx` has the rights to read files in the `/var/www` directory structure
* Determine the `nginx` user by checking the `user` setting in the `nginx` config file:
```
# cat /etc/nginx/nginx.conf |grep user
```
Set the permissions for this user in the /var/www` directory structure:
```
# chown -R www-data /var/www
```
Note the IP address of your container or installation
* We'll refer to that as `IP_ADDR` in this lab
```
# ifconfig
```
Test the application from your browser:
* http://IP_ADDR/test.php
Or if you are using a container or subsystem that maps port 80 of the container to another port:
* Example: port 80 maps to 8888: `http://localhost:8888/test.php`


## Install ZendHQ
Review the instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm)
* If not already done, install the Zend repo:
```
# zendphpctl repo install
```
### Install the ZendHQ daemon
In the demo container install `zendhqd`:
```
# apt install zendhqd
```
Review the `zendhqd` configuration at `/etc/php/8.2-zend/mods-available/zendhq.ini`
```
# nano /opt/zend/zendphp/etc/zendhqd.ini
```
Change the following settings in `zendhqd.ini`:
* You need to set these to listen on any IP address
  * e.g. `0.0.0.0` means any IP address
```
zendhqd.daemon_uri = tcp://0.0.0.0:10090
zendhqd.daemon_pub_uri = tcp://0.0.0.0:10092
zendhqd.websocket.interface = 0.0.0.0:10091
```
* Use `CTL+X` to save exit
Install the ZendHQ license
* Copy the license file to: `/opt/zend/zendphp/etc/license`
Start the daemon
* NOTE: you will receive a message regarding a missing license, however the daemon will still run
```
# /etc/init.d/zendhqd start
```
Confirm that the daemon is running
```
# ps -ax |grep zendhqd
```

### Install the ZendHQ PHP Extension
NOTE: the ZendHQ extension needs to be installed on any PHP installation you wish to monitor
* In the case of the lab, install the
ZendHQ PHP extension in the same Docker container
```
# zendphpctl ext install zendhq
```
Confirm that extension is installed:
```
# php -m
```
Review the configuration. Make changes as desired.
```
# nano /etc/php/8.2-zend/mods-available/zendhq.ini
```
Make sure the daemon uri listens on any IP address:
```
zendhq.daemon_uri = tcp://0.0.0.0:10090
```
* Use `CTL+X` to save and exit

Restart PHP-FPM
* `PHP_VER` was set in an earlier instruction
```
# /etc/init.d/php"$PHP_VER"-zend-fpm restart
```
Confirm the process is running
```
# ps -ax |grep php-fpm
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
