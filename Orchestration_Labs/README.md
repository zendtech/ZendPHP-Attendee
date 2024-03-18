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
Access the shell for any container `CONTAINER_NAME`:
* Change `CONTAINER_NAME` to any of the following:
  * orch_nginx
  * orch_zendhq
  * orch_zendphp1
  * orch_zendphp2
```
$ docker exec -it CONTAINER_NAME /bin/bash
```
Make sure `nginx` is running in `orch_nginx`
```
$ docker exec orch_nginx ps |grep nginx
```
If not, be sure to reload it:
```
$ docker exec zendphp_mon /usr/sbin/nginx -s reload
```

## Install the sample application
Prepare the sample application
* Using a graphical file system tool
  * Select the directory where you cloned (or unzipped) this Git repository
  * For this lab we'll call it `/path/to/repo`
  * Copy the entire `mezzio` directory from `/path/to/repo/Basic_Installation_Alpine/mezzio` to `/path/to/repo/Orchestration_Labs/mezzio`
* Using the command line, from a terminal window
  * Change directory to `/path/to/repo`
  * Copy the entire `mezzio` directory from `/path/to/repo/Basic_Installation_Alpine/mezzio` to `/path/to/repo/Orchestration_Labs/mezzio`
Add the following to the `/etc/hosts` file (or equivalent) on your host computer (not the Docker container!)
```
10.10.70.10  zendphp1.local zendphp2.local
```
Make sure the `nginx` container passes PHP requests to the ZendPHP containers:
* Check if the `nginx` container runs PHP from `orch_zendphp1`:
  * `http://zendphp1.local`
  * On default Mezzio splash screen look to the lower right for the server name
* Check if the `nginx` container runs PHP from `orch_zendphp2`:
  * `http://zendphp2.local`
  * On default Mezzio splash screen look to the lower right for the server name

## Configure the ZendHQ Instance
You'll need to do the following in the ZendHQ instance (container name is `orch_zendhq`):
* Reconfigure `zendhqd` to have its websocket listen on any IP address
* Set the IP address of the ZendHQ daemon URI to localost (e.g. `127.0.0.1`)

From a command line / terminal window, shell into the ZendHQ container
```
$ docker exec -it orch_zendhq /bin/bash
```
Update the `zendhqd.ini` file have the websocket interface listen on any IP address
* Open the `nano` editor
```
# nano /opt/zend/zendphp/etc/zendhqd.ini
```
* Make the following changes
* Note that to disable an option put a semi-colon `;` at the start of the line
* To enable an option, remove the starting semi-colon
* Enter `CTRL+X` save and exit
```
zendhqd.websocket.interface = *:10091
;zendhqd.websocket.interface = :10091
;zendhqd.websocket.interface = ::1:10091
zendhqd.daemon_uri = tcp://127.0.0.1:10090
```
Restart `zendhqd`
* Locate the process ID (`PID`)
```
ps |grep zendhqd |grep -v grep
```
Kill the process
* Substitute the process ID discovered above in place of `PID`
```
kill PID
```
Note: the virtual machine will exit and automatically restart with the new settings
* Confirm that `zendhqd` is running:
```
$ docker exec -it orch_zendhq ps
```

## Configure each of the ZendPHP instances
For each of the ZendPHP instances, you'll need to
* Set them both to look for the ZendHQ daemon at IP address `10.10.70.20`
From a command line / terminal window, shell into the ZendHQ container
```
$ docker exec -it orch_zendphp1 /bin/bash
```
Backup the `/etc/zendphp/conf.d/10_zendhq.ini` file
```
cp /etc/zendphp/conf.d/10_zendhq.ini /tmp/zendhq.ini.old
```
Update the `10_zendhq.ini` file have the websocket interface listen on any IP address
* Open the file with `nano`
```
# nano /etc/zendphp/conf.d/10_zendhq.ini
```
* Make the following changes
* Note that to disable an option put a semi-colon `;` at the start of the line
* To enable an option, remove the starting semi-colon
* Enter `CTRL+X` save and exit
```
zendhq.daemon_uri = tcp://10.10.70.20:10091
```
Restart PHP-FPM
* Locate the process ID (`PID`)
* NOTE: the PID is the number to the far left
```
# ps |grep "php-fpm: master process" |grep -v grep
```
Kill the process
* Substitute the process ID discovered above in place of `PID`
```
kill PID
```
Note: the virtual machine may exit and automatically restart with the new settings
* If it doesn't exit, restart PHP-FPM as follows (still inside the container):
```
# /usr/sbin/php-fpm82zend -D
```
Exit the container
```
# exit
```
Confirm that `zendhqd` is running:
```
$ docker exec -it orch_zendphp1 ps
```
Repeat the process for the `zendphp2` instance

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
  * Location: `/path/to/repo/Orchestration_Labs/ZendPHP-ZendHQ-Training.jmx`

## Run the ZendHQ GUI
* Run the GUI as instructed
  * See: [https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm](https://help.zend.com/zendphp/current/content/installation/zendhq_user_interface_installation.htm)
  * IP Address: `10.10.70.20`
  * User
    * Enter `admin`
  * Token
    * Enter `zendphp`

## Using ZendHQ
The `/api/query` API has occasional database errors and performance issues (by design).
* To run a set of test queries use `path/to/repo/Orchestration_Labs/make_calls.sh`
* Usage:
```
Usage: make_calls.sh forecast|query [NUM] [ZRAY_TOKEN]
       forecast|query : Weather forecast | Postcode lookup
       NUM : number of calls to make
       ZRAY_TOKEN : Copy and paste the Z-Ray token
```
* To simulate a heavy load (well ... 60 users anyhow!):
  * Run Apache JMeter
  * Load the test plan at `path/to/repo/Orchestration_Labs/ZendPHP-ZendHQ-Training.jmx`

### Z-Ray
Test that Z-Ray is responding
* From the ZendHQ GUI
  * Locate and copy the session token (top right) (`TOKEN`)
  * Select Z-Ray Live
* From the browser on your host computer
  * Enter this URL: `http://zendphp.local/api/forecast?zraytok=TOKEN`
* From the ZendHQ GUI
  * Check for a result

### Monitoring


### Database Query Introspection
TBD

## RBAC
TBD

