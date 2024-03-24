# Demo Installation

This demo has ZendPHP and ZendHQ configured in a single Alpine Linux based container.
You can use this container to practice using ZendHQ and the GUI

## Demo Setup
Copy the demo application:
```
cp -r /path/to/repo/Basic_Installation/mezzio/* \
      /path/to/repo/Demo_Installation/mezzio/*
```
Copy the ZendHQ license
```
cp /path/to/license ./docker/license
```
Build the image
```
docker-compose build
```
Bring the image online
```
docker-compose up -d
```
Shell into the container
```
docker run -it zendphp_demo /bin/bash
```
Build the demo app
```
cd /var/www/mezzio
php composer.phar self-update
php composer.phar update
```
Make sure `zendhqd`, `php-fpm` and `nginx` are all running:
```
ps
```
Access the demo from curl or from a browser:
* `http://10.10.80.10/`
* `http://localhost:8080/`
Make repetitve calls:
* From the host computer (not in the container)
```
$ cd /path/to/repo/Demo_Installation
./make_calls.sh
```
Run the ZendHQ GUI
* Install or use the ZendHQ GUI as per these instructions:
  * https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm
* Use these credentials:
  * _Hostname/IP_ : 10.10.80.10
  * _User name_   : admin
  * _User token_  : zendphp

