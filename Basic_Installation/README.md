# Basic Installation Labs

This lab gives you practice installing ZendPHP and ZendHQ
* Clone, or download and unzip this repository:
  * https://github.com/zendtech/ZendPHP-Attendee.git
* This container is based on Alpine Linux
* The labs work best in a Linux VM or a Linux computer
* If you're using Windows:
  * This lab can be performed under Windows Subsystem for Linux (WSL)
    * However you'll run into routing issues as Windows is incapable of routing to the container
  * We recommend installing VirtualBox and Vagrant
    * Use the `Vagrantfile` provided by your setup instructions
    * Or use the `Vagrantfile` in the `Course_Assets` folder
  * You also run the lab after installing Docker Desktop
    * However, you'll run into the same routing issue
* If you're using a Mac:
  * You should be able to run this lab after installing Docker Desktop
* See the `README.md` file in the top folder of this repository for more information
  * https://github.com/zendtech/ZendPHP-Attendee/blob/master/README.md
**IMPORTANT** : in these instructions we indicate commands issuesd at the command prompt as a root user using the `#` symbol
* *DO NOT* incude `#` into the commands you type!
* This is a convention in Linux to indicate a command-line command issued by the "root" user
If you see a command that's preceede by `$` instead of `#`:
  * These commands are issued from a command prompt on your host computer
  * Do not include `$` in the commands you type!

## Build the Image
* Make sure you have Docker Desktop or Docker CE + Docker Compose installed (see `/path/to/repo/README.md`)
* Open a terminal window and change to this directory (e.g. `/path/to/repo/Basic_Installation`)
* Build the images:
```
$ docker-compose build
```
* Run the images (use the `-d` option to run in background)
```
$ docker-compose up -d
```

## Install ZendPHP
Follow these instructions to install ZendPHP from a terminal window in this directory (e.g. `/path/to/repo/Basic_Installation`)
* Open a shell into the ZendPHP container:
```
$ docker exec -it zendphp /bin/bash
```
Read through these instructions to get an overview of the installation:
  * [https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm](https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm)
### Install `zendphpctl`
Download the zendphpctl script and its signature from our repository:
```
# curl -L https://repos.zend.com/zendphp/zendphpctl -o zendphpctl
# curl -L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig
```
Validate the signature:
```
# echo "$(cat zendphpctl.sig) zendphpctl" | sha256sum -c
```
If the signature is valid, remove the signature file, set permissions for the script, and move it into the path for the root user:
```
# rm zendphpctl.sig
# chmod +x zendphpctl
# mv ./zendphpctl /usr/sbin
```
### Install PHP using `zendphpctl`
Follow the instructions here in the "Installation using Zendphpctl":
* [https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm](https://help.zend.com/zendphp/current/content/installation/zendphp_alpinelinux.htm)
* Change `PHP_VER` to the desired PHP version (e.g. `8.2`)
```
# export PHP_VER=8.2
# echo "https://repos.zend.com/zendphp/apk_alpine318/" >> /etc/apk/repositories
# wget https://repos.zend.com/zendphp/apk_alpine318/zendphp-alpine-devel.rsa.pub -O /etc/apk/keys/zendphp-alpine-devel.rsa.pub
# apk update
# zendphpctl repo install
# zendphpctl php install $PHP_VER
```
Test for success:
```
# php -v
PHP 8.2.16 (cli) (built: Feb 27 2024 09:48:40) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.16, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.16, Copyright (c), by Zend Technologies
```
Check for installed modules:
```
# php -m
```
## Install an Extension
Install PDO, PDO_SQLite and cURL (needed to run the demo app)
* Check to see if extension is already installed:
```
# zendphpctl ext list-enabled
```
If not already installed, install the PDO extension:
```
# zendphpctl ext install pdo
```
If not already installed, install the PDO_SQLite extension:
```
# zendphpctl ext install pdo_sqlite
```
If not already installed, install the curl extension:
```
# zendphpctl ext install curl
```

## Install PHP-FPM
Check if FPM support is installed (fpm is-installed)
```
# zendphpctl fpm is-installed
```
Install PHP-FPM for the default version
* NOTE: `PHP_VER` is the environment variable you set above
```
# zendphpctl fpm install $PHP_VER
```
Configure PHP-FPM support
* For now just review the configuration
* Use the defaults for the lab
* IMPORTANT: make a note of the PHP user
  * In this lab we'll call it `PHP_USER`
* Use `CTL+X` to save and exit
```
# export EDITOR=/usr/bin/nano
# zendphpctl fpm config
```
Start PHP-FPM
* Substitute the PHP version in place of `PHP_VER_ALPINE`
* In Alpine Linux don't add a period between the major and minor number
* Example: PHP 8.2 would be "82"
```
# export PHP_VER_ALPINE=82
# /usr/sbin/php-fpm"$PHP_VER_ALPINE"zend
```
Confirm PHP-FPM is running
```
# ps
```
## Configure nginx for the application and PHP-FPM
Open or create a file `/etc/nginx/http.d/default.conf`
```
# nano /etc/nginx/http.d/default.conf
```
Overwrite the contents with the following:
* Use `CTL+X` to save and exit
```
server {
    listen                  80;
    root                    /var/www/mezzio/public;
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
        fastcgi_param     SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param     SCRIPT_NAME      $fastcgi_script_name;
    }
}
```
Restart nginx
```
# /usr/sbin/nginx -s reload
```
## Set up the demo PHP application
Shell into the container
```
$ docker exec -it zendphp /bin/bash
```
Use Composer to update/install the demo app
```
# cd /home/training/mezzio
# php composer.phar self-update
# php composer.phar install
```
Test the application from inside the container:
```
# curl -X GET -H 'Accept: application/json' http://10.10.60.10/api/query
```
Test the application from your browser:
* http://10.10.60.10/api/query
* or:
* http://localhost:8888/api/query

## Install ZendHQ
Review the instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm)
* If not already done, install the Zend repo:
```
# zendphpctl repo install
```
### Install the ZendHQ daemon
This is installed on the server running ZendHQ
```
# apk add zendhqd
```
Review the `zendhqd` configuration at `/opt/zend/zendphp/etc/zendhqd.ini`
```
# nano /opt/zend/zendphp/etc/zendhqd.ini
```
Change the setting for `zendhqd.websocket.interface`
* This needs to listen to external requests on all IP interfaces
* For the lab we use IP v4
* Uncomment this line by removing the leading semi-colon (`;`)
```
zendhqd.websocket.interface = *:10091
```
* Comment-out these lines by adding a leading semi-colon (`;`)
* Use `CTL+X` to save exit
```
# Listen on the IPv4 loopback address only and port number 10091
;zendhqd.websocket.interface = :10091
# Listen on the IPv6 loopback address only and port number 10091
;zendhqd.websocket.interface = ::1:10091
```
### Install your license
Exit the container
```
# exit
```
From your host computer, copy your license from its current location into this folder
```
$ cp /path/to/license/license /path/to/repo/Basic_Installation/license
```
Re-enter the container
```
$ docker exec -it zendphp /bin/bash
```
Move or copy the license from the shared home directory to `/opt/zend/zendphp/etc/`
```
# mv /home/training/license /opt/zend/zendphp/etc/license
```

### Start the daemon
Start the daemon
```
# /opt/zend/zendphp/bin/zendhqd -D -c /opt/zend/zendphp/etc/zendhqd.ini
```
Confirm that the daemon is running
```
# ps
```

### Install the ZendHQ PHP Extension
NOTE: the ZendHQ extension needs to be installed on any PHP installation you wish to monitor
* In this lab, install the ZendHQ PHP extension in the same Docker container
* Note that you need to re-export `PHP_VER` because you previously exited the container
```
# export PHP_VER=8.2
# zendphpctl ext install --php $PHP_VER zendhq
```
Confirm that extension is installed:
```
# php -m
```
Review the configuration. Make changes as desired.
* Note that you need to re-export `PHP_VER_ALPINE` because you previously exited the container
* You can use a new version of PHP if available. The example uses "82".
* Use `CTL+X` to save and exit
```
# export PHP_VER_ALPINE=82
# nano /etc/php/"$PHP_VER_ALPINE"zend/conf.d/10_zendhq.ini
```
Restart PHP-FPM
* NOTE: on Debian/Ubuntu or RHEL/Fedora/CentOS systems PHP-FPM will be running using a run service
* For Alpine Linux you need to kill the master process and restart it
Find the process ID (`PID`) for the PHP-FPM master process:
```
# ps |grep php-fpm
```
Kill the `master process`
* Substitute the PID number in place of "PID":
```
# kill PID
```
Restart PHP-FPM
```
# /usr/sbin/php-fpm"$PHP_VER_ALPINE"zend
```
Confirm the process is running
```
# ps |grep php-fpm
```
## Install the ZendHQ GUI
The ZendHQ user interface runs separately from the web server
* Install the GUI on your local computer (*not* inside the container!)
Review the GUI instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
* The downloads are located here:
  * [https://downloads.zend.com/zendphp/zendhq-ui/](https://downloads.zend.com/zendphp/zendhq-ui/)
Grab the appropriate compressed download file
* Example shown is a Linux command
```
$ wget https://downloads.zend.com/zendphp/zendhq-ui/zendhq-ui-linux-release.tar.gz
```
Extract them using your preferred archiving tool
* Example shown is a Linux command
```
$ tar -xvf ./zendhq-ui-linux-release.tar.gz
```
Run the binary
* NOTE: there are different binaries for different CPU architectures
```
# e.g. for Linux on an Intel _x86 CPU:
$ ./zend-hq-linux_x64
```
For the initial login, enter the following:
* Hostname/IP: `10.10.60.10`
* User name: `admin`
* User token: `zendphp`

## Test Monitoring
Exit the container:
```
# exit
```
Copy the following files into the shared training home directory:
```
$ mkdir /path/to/repo/html
$ cp /path/to/repo/Course_Assets/lookup-app/* /path/to/repo/html
$ cp /path/to/repo/Course_Assets/sample_data/US_Post_Codes.txt /path/to/repo/html
```
Re-enter the container
```
$ docker exec -it zendphp /bin/bash
```
Move or copy the sample app:
```
mv /home/training/html/make_calls.sh /home/training/make_calls.sh
mv /home/training/html/* /var/www/html
```
Use `make_calls.sh` to make 100 calls to the sample app
* Usage:
```
# /home/training/make_calls.sh [ZRAY_TOK]
```
* Copy and paste the Z-Ray token from the ZendHQ GUI as the optional argument
