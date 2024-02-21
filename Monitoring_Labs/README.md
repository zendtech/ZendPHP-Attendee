# Monitoring/Automation Labs

Use this set of containers to practice monitoring and automation with ZendHQ

**IMPORTANT**: if you're running Windows:
* There will be a problem accessing the containers because Windows fails to route between containers
* You will need to set up the course VM using VirtualBox
* You can run this lab inside the course VM

## Preset the ZendHQ Container configuration and license
Copy the license file you obtained before class into the `docker` folder
* The Dockerfile for the ZendHQ container will place this into the `/entrypoint.d` folder
* When the container starts, it will use this license
* Be sure to name the file `license`
Modify `zendhqd.ini` as desired
* The Dockerfile for the ZendHQ container will place this into the `/entrypoint.d` folder
* When the container starts, `zendhqd` will use these INI params
Modify `default_monitor_rules.json` as desired
* The Dockerfile for the ZendHQ container will place this into the `/entrypoint.d` folder
* When the container starts, `zendhqd` will use follow these rules as default

## Build and run the orchestrated system
Build the orchestrated containers as follows:
```
$ docker-compose build
```
Run the system as follows:
```
$ docker-compose up
```
Access the shell for any container:
* Choose only 1 of:
  * nginx_mon
  * zendhq_mon
  * zendphp_mon_1
  * zendphp_mon_2
```
$ docker exec -it nginx_mon|zendhq_mon|zendphp_mon_1|zendphp_mon_2 /bin/bash
```

## Install the sample application
From your host system (not in the Docker container) open a command prompt or terminal window
* Change to the directory where you cloned (or unzipped) this Git repository
  * For this lab we'll call it `/path/to/repo`
* Copy the entire `mezzio` directory from `/path/to/repo/mezzio` to /path/to/repo/Monitoring_Labs/mezzio`
* Restart the orchestrated system
```
$ docker-compose restart
```
Update the app using composer
* Shell into the `nginx_mon` container
```
$ docker exec -it nginx_mon /bin/bash
```
Change to the `mezzio` folder in the container and update using Composer
```
# cd /var/www/messzio
# php composer.phar self-update
# php composer.phar install
```
Add the following to the `/etc/hosts` file (or equivalent) on your host computer:
```
10.10.70.10  zendphp1.local zendphp2.local
```
Make sure the `nginx` container passes PHP requests to the ZendPHP containers:
* Check if the `nginx` container runs PHP from `zendphp_mon_1`:
  * `http://zendphp1.local`
* Check if the `nginx` container runs PHP from `zendphp_mon_2`:
  * `http://zendphp2.local`
* In both cases you should see the default Mezzio splash screen

## Run the ZendHQ GUI
* Run the GUI as instructed
  * See: [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
  * IP Address: `10.10.70.40`
  * User
    * Enter `admin`
  * Token
    * Enter `zendphp`

## Performance Monitoring
TBD

## Database Monitoring
TBD

## RBAC
TBD

