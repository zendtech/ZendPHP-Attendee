#!/bin/bash

set -e

function apt-get() {
    while fuser -s /var/lib/dpkg/lock;do
        echo "apt-get is waiting for the lock release..."
        sleep 1
    done

    while fuser -s /var/cache/debconf/config.dat;do
        echo "apt-get is waiting for the lock release..."
        sleep 1
    done

    sudo /usr/bin/apt-get "$@"
}

# Install necessary dependencies
echo 'debconf debconf/frontend select Noninteractive' | sudo debconf-set-selections
apt-get update
apt-get -y -qq install \
    curl \
    wget \
    apt-transport-https \
    ca-certificates

# Setup sudo to allow no-password sudo for "zendphp" group and adding "terraform" user
sudo groupadd -r zendphp
sudo useradd -m -s /bin/bash terraform
sudo usermod -a -G zendphp terraform
sudo cp /etc/sudoers /etc/sudoers.orig
echo "terraform ALL=(ALL) NOPASSWD:ALL" | sudo tee /etc/sudoers.d/terraform

# Installing SSH key
sudo mkdir -p /home/terraform/.ssh
sudo chmod 700 /home/terraform/.ssh
sudo cp /tmp/terraform-ssh-key.pub /home/terraform/.ssh/authorized_keys
sudo chmod 600 /home/terraform/.ssh/authorized_keys
sudo chown -R terraform /home/terraform/.ssh
sudo usermod --shell /bin/bash terraform

# Install application
echo "Installing the application source"
sudo mkdir -p /var/local/app
cd /var/local/app
sudo tar xzf /tmp/app.tgz --strip-components=1
sudo chown -R www-data.www-data /var/local/app

# Fix nginx issue; see https://stackoverflow.com/questions/13895933/nginx-emerg-could-not-build-the-server-names-hash-you-should-increase-server
echo "Fixing nginx configuration"
sudo /bin/bash -c 'echo "server_names_hash_bucket_size 128;" > /etc/nginx/conf.d/http.conf'

echo "Adding the default vhost"
# disable error checking, as this script emits an error status always
set +e
sudo zendphp-vhost add _
sudo zendphp-switch to 7.4 _
set -e
sudo sed -i -r -e 's/server_name _;/server_name _ default_server;/' /etc/nginx/sites-available/_.conf
sudo rm /etc/nginx/sites-enabled/default
apt-get -y -qq install php7.4-zend-redis

echo "Removing the generated vhost root directory (/var/www/_)"
sudo rm -rf "/var/www/_"

echo "Symlinking /var/local/app/public to the vhost root directory location"
sudo ln -s /var/local/app/public "/var/www/_"

echo "Restarting nginx"
sudo systemctl restart nginx
