# ZendPHP / ZendHQ WSL Test : 2022-07-09

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
Install PHP 8.1
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
## ZendHQ Installation
```
acer@DESKTOP-2KDGL2A:/tmp/php8.1-zend$ sudo apt install php8.1-zend-zendhq
Reading package lists... Done
Building dependency tree
Reading state information... Done
The following package was automatically installed and is no longer required:
  libfwupdplugin1
Use 'sudo apt autoremove' to remove it.
The following additional packages will be installed:
  apache2 apache2-bin apache2-data apache2-utils libapache2-mod-php8.1-zend libapr1 libaprutil1 libaprutil1-dbd-sqlite3 libaprutil1-ldap libjansson4 liblua5.2-0
  libprotobuf28-zendphp libre2-5 libzmq5-zendphp php-zray-plugins php8.1-zend ssl-cert zendhq-libs
Suggested packages:
  apache2-doc apache2-suexec-pristine | apache2-suexec-custom openssl-blacklist
The following NEW packages will be installed:
  apache2 apache2-bin apache2-data apache2-utils libapache2-mod-php8.1-zend libapr1 libaprutil1 libaprutil1-dbd-sqlite3 libaprutil1-ldap libjansson4 liblua5.2-0
  libprotobuf28-zendphp libre2-5 libzmq5-zendphp php-zray-plugins php8.1-zend php8.1-zend-zendhq ssl-cert zendhq-libs
0 upgraded, 19 newly installed, 0 to remove and 0 not upgraded.
Need to get 5523 kB of archives.
After this operation, 25.0 MB of additional disk space will be used.
Do you want to continue? [Y/n] Y
...
Setting up libapache2-mod-php8.1-zend (8.1.7-1) ...
Package apache2 is not configured yet. Will defer actions by package libapache2-mod-php8.1-zend.
Creating config file /etc/php/8.1-zend/apache2/php.ini with new version
No module matches
Setting up apache2 (2.4.41-4ubuntu3.12) ...
Enabling module mpm_event.
Enabling module authz_core.
Enabling module authz_host.
Enabling module authn_core.
Enabling module auth_basic.
Enabling module access_compat.
Enabling module authn_file.
Enabling module authz_user.
Enabling module alias.
Enabling module dir.
Enabling module autoindex.
Enabling module env.
Enabling module mime.
Enabling module negotiation.
Enabling module setenvif.
Enabling module filter.
Enabling module deflate.
Enabling module status.
Enabling module reqtimeout.
Enabling conf charset.
Enabling conf localized-error-pages.
Enabling conf other-vhosts-access-log.
Enabling conf security.
Enabling conf serve-cgi-bin.
Enabling site 000-default.
info: Switch to mpm prefork for package libapache2-mod-php8.1-zend
Module mpm_event disabled.
Enabling module mpm_prefork.
info: Executing deferred 'a2enmod php8.1-zend' for package libapache2-mod-php8.1-zend
Enabling module php8.1-zend.
Created symlink /etc/systemd/system/multi-user.target.wants/apache2.service → /lib/systemd/system/apache2.service.
Created symlink /etc/systemd/system/multi-user.target.wants/apache-htcacheclean.service → /lib/systemd/system/apache-htcacheclean.service.
invoke-rc.d: could not determine current runlevel
Setting up php8.1-zend (8.1.3-2) ...
Setting up php8.1-zend-zendhq:amd64 (1.1.0-1) ...
/var/lib/dpkg/info/php8.1-zend-zendhq:amd64.postinst: 39: [: missing ]
Processing triggers for ufw (0.36-6ubuntu1) ...
Processing triggers for systemd (245.4-4ubuntu3.17) ...
Processing triggers for man-db (2.9.1-1) ...
Processing triggers for libc-bin (2.31-0ubuntu9.9) ...
Processing triggers for libapache2-mod-php8.1-zend (8.1.7-1) ...
Processing triggers for php8.1-zend-cli (8.1.7-1) ...
```
Confirm Installation
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo ./zendphpctl ext-list-enabled
calendar
ctype
exif
ffi
fileinfo
ftp
gettext
iconv
opcache
pdo
phar
posix
readline
shmop
sockets
sysvmsg
sysvsem
sysvshm
tokenizer
zendhq
acer@DESKTOP-2KDGL2A:~/zendphp$
```
Install zendhqd
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo apt install zendhqd
Reading package lists... Done
Building dependency tree
Reading state information... Done
...
The following NEW packages will be installed:
  freetds-common libmariadb3 libodbc1 libpq5 libqt5-zendphp libsybdb5 mariadb-common mysql-common zendhqd
0 upgraded, 9 newly installed, 0 to remove and 0 not upgraded.
Need to get 5641 kB of archives.
After this operation, 19.1 MB of additional disk space will be used.
Do you want to continue? [Y/n] Y
...
Setting up zendhqd:amd64 (1.1.0-1) ...
zendhqd: not a valid unit name "zendhqd": Invalid argument
Created symlink /etc/systemd/system/multi-user.target.wants/zendhqd.service → /lib/systemd/system/zendhqd.service.
invoke-rc.d: could not determine current runlevel
Processing triggers for systemd (245.4-4ubuntu3.17) ...
Processing triggers for man-db (2.9.1-1) ...
Processing triggers for libc-bin (2.31-0ubuntu9.9) ...
```
Configure ZendHQ
```
acer@DESKTOP-2KDGL2A:~/zendphp$ cat /opt/zend/zendphp/etc/zendhqd.ini |grep zendhqd
zendhqd.log_file = /opt/zend/zendphp/var/log/zendhqd.log
; zendhqd.pid_file = /opt/zend/zendphp/var/run/zendhqd.pid
; zendhqd.daemonize = 0
; zendhqd.log_verbosity_level = 2
; zendhqd.tmp_dir = /tmp
; zendhqd.user = -1
; zendhqd.group = -1
zendhqd.extensions_dir = /opt/zend/zendphp/lib
zendhqd.extension = zendhq_session
zendhqd.session.timeout = 600
zendhqd.session.auth_token_hash = 8cb3000d2add8d41459625bc5a7a6139628b4d3d59ae512c549f87470d6b2481
zendhqd.extension = zendhq_conf
zendhqd.conf.database_path = /opt/zend/zendphp/var/db/conf.db
zendhqd.extension = zendhq_zmq
; zendhqd.daemon_uri = ipc:///tmp/z_ray.sock
zendhqd.daemon_uri = tcp://0.0.0.0:10090
zendhqd.daemon_pub_uri = tcp://0.0.0.0:10092
zendhqd.extension = zendhq_ws
;zendhqd.websocket.interface = *:10091
zendhqd.websocket.interface = *:10091
;zendhqd.websocket.interface = ::1:10091
zendhqd.extension = zendhq_sock
;zendhqd.socket.interface = *:10093
;zendhqd.socket.interface = 127.0.0.1:10093
;zendhqd.socket.interface = ::1:10093
zendhqd.extension = zray_mq
zendhqd.extension = zray_db
zendhqd.zray_db.database_path = /opt/zend/zendphp/var/db/z_ray.db
zendhqd.zray_db.cleanup_frequency = 10
zendhqd.zray_db.history_time = 7
zendhqd.zray_db.history_requests = 10000
zendhqd.extension = zendhq_monitor
zendhqd.monitor.database_path = /opt/zend/zendphp/var/db/monitor.db
#zendhqd.monitor.aggregate_events = 1
zendhqd.monitor.cleanup_frequency = 10
zendhqd.monitor.history_time = 30
```
Start zendhqd
```
acer@DESKTOP-2KDGL2A:~/zendphp$ sudo /etc/init.d/zendhqd start
Starting ZendHQ Daemon  done
acer@DESKTOP-2KDGL2A:~/zendphp$ ps -ax
  PID TTY      STAT   TIME COMMAND
    1 ?        Sl     0:00 /init
    8 ?        Ss     0:00 /init
    9 ?        R      0:02 /init
   10 pts/0    Ss     0:01 -bash
15586 ?        Ssl    0:00 /opt/zend/zendphp/bin/zendhqd --daemonize --application zendhqd --config-file /opt/zend/zendphp/etc/zendhqd.ini --pid-file /opt/zend/zendphp/var/r
15591 pts/0    R+     0:00 ps -ax
```

## Run the GUI
Get IP address
```
acer@DESKTOP-2KDGL2A:~/zendphp$ ifconfig
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 172.24.119.187  netmask 255.255.240.0  broadcast 172.24.127.255
        inet6 fe80::215:5dff:fe7d:940a  prefixlen 64  scopeid 0x20<link>
        ether 00:15:5d:7d:94:0a  txqueuelen 1000  (Ethernet)
        RX packets 53122  bytes 275177889 (275.1 MB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 31237  bytes 2196819 (2.1 MB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0
```
Make sure I can see it (from PowerShell)
```
PS C:\WINDOWS\system32> ping 172.24.119.187

Pinging 172.24.119.187 with 32 bytes of data:
Reply from 172.24.119.187: bytes=32 time<1ms TTL=64
Reply from 172.24.119.187: bytes=32 time<1ms TTL=64
Reply from 172.24.119.187: bytes=32 time<1ms TTL=64
Reply from 172.24.119.187: bytes=32 time<1ms TTL=64

Ping statistics for 172.24.119.187:
    Packets: Sent = 4, Received = 4, Lost = 0 (0% loss),
Approximate round trip times in milli-seconds:
    Minimum = 0ms, Maximum = 0ms, Average = 0ms
```
Ran the GUI
* Blank Screen
Run the GUI as Administrator
* Blank Screen

**STOPPED TESTING**
2022-07-11
