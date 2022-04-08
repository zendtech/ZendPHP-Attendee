#!/bin/bash
# enable the YAML extension
phpenmod -v 8.1-zend -s cli yaml
# install git + restore course repo
apt-get install -y git
cd /opt
git clone https://github.com/zendtech/ZendPHP-Attendee.git app
