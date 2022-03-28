# ZendPHP JumpStart -- Attendee Files

## Ubuntu Installation Notes
When adding the correct `apt` sources repo:
* Look here: http://repos.zend.com/zendphp/
Example:
```
deb https://repos.zend.com/zendphp/deb_debian10/ zendphp non-free
```

To get a list of package names, browse:
* http://repos.zend.com/zendphp/deb_ubuntu2004/dists/zendphp/non-free/binary-amd64/Packages
* Hint: Scroll all the way to the bottom and then work backwards

Example PHP packages:
* php7.4-zend
* php8.1-zend

## Docker Image Notes
Pull a PHP 8.1 image with an Ubuntu base:
* From a command prompt
```
docker pull cr.zend.com/zendphp/8.1:ubuntu-20.04-cli
```

Built-in PHP webserver demo:
* From a command prompt
```
cd /path/to/mezzio/project
docker run -v `pwd`:/home --network host -it 74ca5659fe61 -S localhost:8888 -t /home/public
```
* From browser: http://localhost:8888

## CentOS Notes
To get a list of package names, browse:
* `http://repos.zend.com/zendphp/rpm_centosN`
* Where `N` could be 6, 7 or 8

Example PHP packages:
* php74zend
* php81zend

Save time on installation by adding the `-y` option:
```
sudo yum install -y php{PHP_VERSION}zend
```

## nginx / php-fpm Notes
nginx conf:
* Add this to /etc/nginx/nginx.conf:
```
location ~* \.php$ {
    # With php-fpm unix sockets
    fastcgi_pass unix:/var/opt/zend/php74zend/run/php-fpm/www.sock;
    include         fastcgi_params;
    fastcgi_param   SCRIPT_FILENAME    $document_root$fastcgi_script_name;
    fastcgi_param   SCRIPT_NAME        $fastcgi_script_name;
}
```
* Allow nginx access to `www.sock`
```
sudo chgrp nginx /var/opt/zend/php74zend/run/php-fpm/www.sock
```
* Disable Apache if running:
```
sudo systemctl disable httpd
```
* Open up CentOS firewall for nginx
```
firewall-cmd --zone=public --permanent --add-service=http
firewall-cmd --zone=public --permanent --add-service=https
firewall-cmd --reload
```

## Windows Notes
Installing PHP 8.1 with all extensions enabled except for pdo_oci, pdo_pgsql, pgsql and oci8:
* From a PowerShell prompt:
```
PS C:\Users\ACER\ZendPHP> .\zendphp_install.ps1 install 8.1 \
    -enable-all \
    -with-deps mibs \
    -set-system-path \
    -disable pdo_oci,pdo_pgsql,pgsql,oci8
```
* Output:
```
Installing ZendPHP 8.1 to: C:\zendphp\8.1
Installing from https://repos.zend.com/zendphp/windows/latest                                                                                        Skipping mibs installation; already installed in c:\usr\share\snmp\mibs                                                                              Installing ZendPHP 8.1                                                                                                                               Downloading from https://repos.zend.com/zendphp/windows/latest/zendphp-8.1-latest-nts-Win32-vs16-x64.zip to zendphp-8.1-latest-nts-Win32-vs16-x64.zipSetting system path
Path has already been set; skipping
Configuring ZendPHP 8.1 in C:\zendphp\8.1
```
* Testing the version:
```
PS C:\Users\ACER\ZendPHP> php --version
PHP 8.1.3 (cli) (built: Feb 18 2022 09:06:10) (NTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.1.3, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.3, Copyright (c), by Zend Technologies
PS C:\Users\ACER\ZendPHP>
```

* `zendphp_install.ps1` script usage:
```
PS C:\Users\ACER\ZendPHP> .\zendphp_install.ps1
Usage:

  zendphp_install.ps1 <sub-command> [<php-version>] [options]

Sub-commands:

  help       : This usage message.
  install    : Install/Upgrade the ZendPHP version, and/or any dependencies or non-standard PECL extensions.
               You may also enable/disable extensions at this time.
  config     : Configure your ZendPHP version (enable or disable extensions).

Options:
  -deps-only                : do not install PHP; only install requested PECL extensions or dependency libraries
  -development              : install the development php.ini file (default is production; first install of version only)
  -disable <ext-list>       : disable specific extensions, separator is comma (see internal and PECL lists below)
  -disable-all              : disable all loadable extensions in php.ini
  -enable <ext-list>        : enable specific extensions, separator is comma (see internal extensions list below)
  -enable-all               : enable all extensions found in ext directory in php.ini
  -pecl <ext-list>          : list of pecl extensions packages to download, separated by comma (see PECL list below)
  -repo <url>               : root of repository url; only specify when using a private repository
  -repo-password <pass>     : specify password for accessing LTS versions
  -repo-username <username> : specify username for accessing LTS versions
  -set-system-path          : store path for machine environment
  -target-path <path>       : specifies where to install php files. Default is c:\zendphp\<major.minor>
  -ts                       : install a thread-safe build instead of non-thread-safe
  -with-deps <list>         : install additional dependencies; separator is comma. (see dependency libraries list below)

Valid PHP versions are:
  - 7.1
  - 7.2
  - 7.3
  - 7.4
  - 8.0
  - 8.1

Valid internal extensions (-enable, -disable) are:
  - bz2
  - com_dotnet
  - curl
  - enchant
  - exif
  - ffi
  - fileinfo
  - ftp
  - gd
  - gettext
  - gmp
  - imap
  - intl
  - ldap
  - mbstring
  - memcache
  - mysqli
  - oci8
  - odbc
  - opcache
  - openssl
  - pdo_mysql
  - pdo_oci
  - pdo_odbc
  - pdo_pgsql
  - pdo_sqlite
  - pgsql
  - phar
  - shmop
  - snmp
  - soap
  - sockets
  - sodium
  - sqlite3
  - ssh2
  - sysvshm
  - tidy
  - tokenizer
  - xsl

Valid PECL extensions (-pecl, -disable) are:
  - igbinary
  - imagick
  - memcache
  - mongodb
  - pdo_sqlsrv
  - redis
  - sodium
  - sqlsrv
  - ssh2
  - win32service
  - xdebug

Dependency libraries (-with-deps) this installer can install:
  - debug (debug symbols)
  - mibs (Management Information Base libraries; required for SNMP extension)
  - oci (Oracle Instant Client libraries; required for oci8/pdo_oci extension)
  - pgsql (PostgreSQL client libraries; required for pgsql/pdo_pgsql extension)
  - vcruntime (Visual C++ runtime; required to run ZendPHP)
```

Other Examples
* Full install:
```
zendphp_install.ps1 install 8.1 -enable-all -with-deps mibs -set-system-path
```

* Disable bz2 and xdebug after install:
```
zendphp_install.ps1 config 8.1 -disable bz2,xdebug
```

* Enable `gd` in a custom install directory:
```
zendphp_install.ps1 config -target-path C:\php -enable gd
```
