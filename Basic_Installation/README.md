# Basic Installation Labs

This lab gives you practice installing ZendPHP and ZendHQ
* See the `README.md` file in the top folder of this repository for more information
  * https://github.com/zendtech/ZendPHP-Attendee/blob/master/README.md
**IMPORTANT** : in these instructions we indicate commands issuesd at the command prompt as a root user using the `#` symbol
* *DO NOT* incude `#` into the commands you type!
* This is a convention in Linux to indicate a command-line command issued by the "root" user
If you see a command that's preceede by `$` instead of `#`:
  * These commands are issued from a command prompt on your host computer
  * Do not include `$` in the commands you type!

## Install ZendPHP
Login to the VM:
* Username: **vagrant**
* Password: **vagrant**
Open a terminal window using `CTL+ALT+T`
* Add to `Favorites` by right clicking the terminal window icon on the left side task bar
Read through these instructions to get an overview of the installation:
  * [https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm](https://help.zend.com/zendphp/current/content/installation/zendphpctl.htm)
Switch to the `root` user:
```
$ sudo -i
```

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
* Change `PHP_VER` to the desired PHP version (e.g. `8.3`)
```
# export PHP_VER=8.3
# apt update
# zendphpctl repo install
# zendphpctl php install $PHP_VER
```
Test for success:
```
# php -v
PHP 8.3.16 (cli) (built: Feb 27 2024 09:48:40) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.16, Copyright (c) Zend Technologies
    with Zend OPcache v8.3.16, Copyright (c), by Zend Technologies
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
```
# /etc/init.d/php$PHP_VER-zend-fpm start
```
Confirm PHP-FPM is running
```
# ps
```
## Set up the demo PHP application
Use Composer to update/install the demo app
```
# cd /home/vagrant/Zend/mezzio
# composer install
```
Connect the demo app to the web server directory structure:
```
# ln -s /home/vagrant/Zend/mezzio /var/www/mezzio
```
## Configure nginx for the application and PHP-FPM
Open or create a file `/etc/nginx/http.d/default`
```
# nano /etc/nginx/http.d/default
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
    location = / {
        try_files $uri /index.php?$query_string;
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
Test the application from inside the container:
```
# curl -X GET -H 'Accept: application/json' http://localhost/api/query
```
Test the application from your browser:
* http://localhost/api/query

## Install ZendHQ
Review the instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_installation.htm)
Open a terminal windows if not already open: `CTL+ALT+T`
If not already done, install the Zend repo:
```
# zendphpctl repo install
```

### Install the ZendHQ daemon
This is installed on the server running ZendHQ
```
# apt install -y zendhqd
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
From your host computer, copy your license from its current location into the new folder for this course
* I.e., the folder that contains the `Vagrantfile`
Switch back to the VM

Move or copy the license from the shared home directory to `/opt/zend/zendphp/etc/`
```
# cp ~/Shared/license /opt/zend/zendphp/etc/license
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
* Note `PHP_VER` is an environment variable you set in an earlier lab
* If you have subsequently exited the terminal window, you'll need to re-export `PHP_VER`
```
# export PHP_VER=8.3
# zendphpctl ext install --php $PHP_VER zendhq
```
Confirm that extension is installed:
```
# php -m
```
Review the configuration. Make changes as desired.
* Use `CTL+X` to save and exit
```
# nano /etc/php/"$PHP_VER"-zend/conf.d/10_zendhq.ini
```
Restart PHP-FPM
```
# /etc/init.d/php$PHP_VER-zend-fpm start
```
Confirm the process is running
```
# ps |grep php-fpm
```
## Install the ZendHQ GUI
The ZendHQ user interface runs separately from the web server
* Normally you would install the GUI on your local computer
* For the purposes of this course you can install the ZendHQ GUI directly in the VM
Review the GUI instructions here:
* [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
* The downloads are located here:
  * [https://downloads.zend.com/zendphp/zendhq-ui/](https://downloads.zend.com/zendphp/zendhq-ui/)
Open a terminal window and change to the home directory
* If you have a window open as `root`, exit `sudo -i` interactive shell
* Return to a shell where you are the `vagrant` user (not `root`)
Download the appropriate compressed ZendHQ GUI file
```
$ cd
$ wget https://downloads.zend.com/zendphp/zendhq-ui/zendhq-ui-linux-release.tar.gz
```
Extract them using your preferred archiving tool
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
* Hostname/IP: `localhost`
* User name: `admin`
* User token: `zendphp`
