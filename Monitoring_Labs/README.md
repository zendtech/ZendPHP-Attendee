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
Prepare the sample application
* Using a graphical file system tool
  * Select the directory where you cloned (or unzipped) this Git repository
  * For this lab we'll call it `/path/to/repo`
  * Copy the entire `mezzio` directory from `/path/to/repo/mezzio` to `/path/to/repo/Monitoring_Labs/mezzio`
* Using the command line, from a terminal window
  * Change directory to `/path/to/repo`
  * Copy the entire `mezzio` directory from `/path/to/repo/mezzio` to `/path/to/repo/Monitoring_Labs/mezzio`

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
  * zendhq_mon
  * zendphp_mon
```
$ docker exec -it zendhq_mon|zendphp_mon /bin/bash
```
Make sure `nginx` is running in `zendphp_mon`
```
$ docker exec zendphp_mon ps |grep nginx
```
If not, be sure to start it:
```
$ docker exec zendphp_mon /usr/sbin/nginx
```

## Install the sample application
Update the app using composer
* Shell into the `zendphp_mon` container
```
$ docker exec -it zendphp_mon /bin/bash
```
Change to the `mezzio` folder in the container and update using Composer
```
# cd /var/www/mezzio
# php composer.phar self-update
# php composer.phar install
```
Exit the container
```
# exit
```
Add the following to the `/etc/hosts` file (or equivalent) on your host computer (not the Docker container!)
```
10.10.70.10  zendphp.local
```
Make sure the `nginx` container passes PHP requests to the ZendPHP containers:
* Check if the `nginx` container runs PHP from `zendphp_mon`:
  * `http://zendphp.local`
  * You should see the default Mezzio splash screen

## Install and Configure Apache JMeter
For the purposes of the monitoring labs we use Apache JMeter to simulate a load
* Install this on your local computer, not in the container
* If not already available, install Java 8 Runtime Environment (JRE)
  * See: [https://www.java.com/en/download/help/download_options.html](https://www.java.com/en/download/help/download_options.html)
* Download the latest Apache JMeter binary (e.g. 5.6.3)
  * [https://jmeter.apache.org/download_jmeter.cgi](https://jmeter.apache.org/download_jmeter.cgi)
  * Download and extract either the `*.zip` or the `*.gz` file at your preference
* Launch JMeter using a script from the `apache-jmeter-VERSION/bin/` folder
  * For Linux or Mac: use `jmeter.sh`
  * For Windows command prompt: use `jmeter.bat`
  * For Windows PowerShell prompt: `jmeter-n.cmd`
* From the GUI, load the test plan
  * Location: `/path/to/repo/Monitoring_Labs/ZendPHP-ZendHQ-Training.jmx`
## Run the ZendHQ GUI
* Run the GUI as instructed
  * See: [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
  * IP Address: `10.10.70.20`
  * User
    * Enter `admin`
  * Token
    * Enter `zendphp`

## Monitoring


## Z-Ray
Test that Z-Ray is responding
* From the ZendHQ GUI
  * Locate and copy the session token (top right)
* From the browser on your host computer
  * Enter this URL: `

## Database Query Introspection
TBD

## RBAC
TBD

