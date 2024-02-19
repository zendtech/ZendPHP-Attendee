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


## Install the sample application
* Open a terminal window and change to this directory (e.g. `/home/training`)
* Change to the `mezzio` directory
* Install the app using composer
```
php composer.phar self-update
php composer.phar install
```
* Test the application from your browser:
  * http://10.10.70.20/
  * or:
  * http://localhost:8881

## Build and run the orchestrated system
Build the orchestrated containers as follows:
```
docker-compose build
```
Run the system as follows:
```
docker-compose up
```
Access the shell for any container:
* Choose only 1 of:
  * zendhq_mon
  * zendphp_mon_1
  * zendphp_mon_2
```
docker exec -it zendhq_mon|zendphp_mon_1|zendphp_mon_2 /bin/bash
```
Use the GUI to communicate with the ZendHQ container:
* Run the GUI as instructed
  * See: [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
* Use this IP address: `10.10.70.10`

