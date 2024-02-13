# Monitoring/Automation Labs

Use this set of containers to practice monitoring and automation with ZendHQ

## Install the sample application
* Open a terminal window and change to this directory (e.g. `/home/training`)
* Change to the `mezzio` directory
* Install the app using composer
```
php composer.phar self-update
php composer.phar install
```
* Test the application from your browser:
  * http://10.10.60.10/
  * or:
  * http://localhost:8888
* Optional:
  * Add `10.60.10.10     zendphp.local` to your `/etc/hosts` file
  * From your browser: `http://zendphp.local/`
