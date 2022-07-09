# ZendPHP / ZendHQ WSL Test

## System
Acer Aspire Desktop
* Device name	DESKTOP-2KDGL2A
* Processor	Intel(R) Core(TM) i5-8400 CPU @ 2.80GHz   2.81 GHz
* Installed RAM	16.0 GB
* Device ID	562212E5-938F-4437-A726-A1E8B2E42ED6
* Product ID	00331-10000-00001-AA879
* System type	64-bit operating system, x64-based processor
* Pen and touch	No pen or touch input is available for this display


Edition	Windows 10 Pro
* Version	21H1
* Installed on	‎2020-‎07-‎25
* OS build	19043.1766
* Experience	Windows Feature Experience Pack 120.2212.4180.0

WSL Info
```
PS C:\WINDOWS\system32> wsl --list --verbose
  NAME                   STATE           VERSION
* Ubuntu-20.04           Running         2
  docker-desktop         Stopped         2
  docker-desktop-data    Stopped         2
```

From the WSL prompt:
```
acer@DESKTOP-2KDGL2A:~$ sudo apt update
acer@DESKTOP-2KDGL2A:~$ sudo apt upgrade
```

## ZendPHP Installation
Install `zendphpctl`
```
acer@DESKTOP-2KDGL2A:~/zendphp$ curl -L https://repos.zend.com/zendphp/zendphpctl -o zendphpctl
L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  124k  100  124k    0     0  63374      0  0:00:02  0:00:02 --:--:-- 63374
acer@DESKTOP-2KDGL2A:~/zendphp$ curl -L https://repos.zend.com/zendphp/zendphpctl.sig -o zendphpctl.sig
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100    65  100    65    0     0     53      0  0:00:01  0:00:01 --:--:--    53
```
Verify signature + make executable
```
acer@DESKTOP-2KDGL2A:~/zendphp$ echo "$(cat zendphpctl.sig) zendphpctl" | sha256sum --check
zendphpctl: OK
acer@DESKTOP-2KDGL2A:~/zendphp$ rm zendphpctl.sig
acer@DESKTOP-2KDGL2A:~/zendphp$ chmod +x zendphpctl
```
Install repo
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl repo-install
Preparing to install ZendPHP repository
...
Setting up apt-transport-https (2.0.9) ...
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1673  100  1673    0     0   1350      0  0:00:01  0:00:01 --:--:--  1350
...
Fetched 77.1 kB in 3s (29.0 kB/s)
Reading package lists... Done
ZendPHP repository installed and configured!
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl php-install 8.1
Installing for DEB system
...
The following NEW packages will be installed:
  php-common php8.1-zend-cli php8.1-zend-common php8.1-zend-opcache php8.1-zend-readline
0 upgraded, 5 newly installed, 0 to remove and 0 not upgraded.
...
Creating config file /etc/php/8.1-zend/cli/php.ini with new version
Processing triggers for man-db (2.9.1-1) ...
Processing triggers for php8.1-zend-cli (8.1.7-1) ...
Setting default PHP version to 8.1
acer@DESKTOP-2KDGL2A:~/zendphp$ php -v
PHP 8.1.7 (cli) (built: Feb 21 2022 10:54:21) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.1.7, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.7, Copyright (c), by Zend Technologies
```
Attempting PECL
```
acer@DESKTOP-2KDGL2A:~/zendphp$ pecl
PHP Fatal error:  Array and string offset access syntax with curly braces is no longer supported in /usr/share/php/PEAR/Config.php on line 2095
acer@DESKTOP-2KDGL2A:~/zendphp$ tar tfvz ./pear.tar.gz
acer@DESKTOP-2KDGL2A:~/zendphp$ find /usr/share/php -exec sudo sed -i 's/{0}/[0]/g' {} \;
acer@DESKTOP-2KDGL2A:~/zendphp$ find /usr/share/php/PEAR -exec sudo sed -i 's/{1}/[1]/g' {} \;
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo sed -i 's/{1}/[1]/g' /usr/share/php/Console/Getopt.php
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo sed -i 's/{2}/[2]/g' /usr/share/php/Console/Getopt.php
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo sed -i 's/{\$i}/[\$i]/g' /usr/share/php/Console/Getopt.php
```
Installing ZendHQ extension
```
Install ZendHQ extension
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl php-set-default 8.1
Setting default PHP version to 8.1
update-alternatives: using /usr/bin/php8.1-zend to provide /usr/bin/php (php) in manual mode
acer@DESKTOP-2KDGL2A:~/zendphp$ php -v
PHP 8.1.7 (cli) (built: Feb 21 2022 10:54:21) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.1.7, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.7, Copyright (c), by Zend Technologies
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl ext-install php8.1-zend-zendhq
Installing the following extensions for ALL: php8.1-zend-zendhq
Installing extension 'php8.1-zend'
- Detected priority: ''
- Detected version: 'zendhq'
Installing from source
Preparing build directory
Downloading php8.1-zend from PECL via https://pecl.php.net/get/php8.1-zend-zendhq.tgz

gzip: stdin: not in gzip format
tar: Child returned status 1
tar: Error is not recoverable: exiting now
Invalid package archive; cannot continue. Please check /tmp/php8.1-zend
Enabling extension
WARNING: Module php8.1-zend ini file doesn't exist under /etc/php/8.1-zend/mods-available
```
**Gave up on 8.1**

## Installing ZendHQ using PHP 7.4
Install PHP 7.4
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl php-install 7.4
acer@DESKTOP-2KDGL2A:~/zendphp$ php -v
PHP 7.4.30 (cli) (built: Feb 21 2022 10:45:21) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
    with Zend OPcache v7.4.30, Copyright (c), by Zend Technologies
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl php-set-default 7.4
Setting default PHP version to 7.4
update-alternatives: using /usr/bin/php7.4-zend to provide /usr/bin/php (php) in manual mode
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl ext-install php7.4-zend-zendhq
Installing the following extensions for ALL: php7.4-zend-zendhq
Installing extension 'php7.4-zend'
- Detected priority: ''
- Detected version: 'zendhq'
Installing from source
Preparing build directory
Downloading php7.4-zend from PECL via https://pecl.php.net/get/php7.4-zend-zendhq.tgz

gzip: stdin: not in gzip format
tar: Child returned status 1
tar: Error is not recoverable: exiting now
Invalid package archive; cannot continue. Please check /tmp/php7.4-zend
Enabling extension
WARNING: Module php7.4-zend ini file doesn't exist under /etc/php/7.4-zend/mods-available
```
