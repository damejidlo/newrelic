#!/bin/bash
echo
echo "Installing NewRelic PHP extension"

echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | sudo tee /etc/apt/sources.list.d/newrelic.list
wget -O- https://download.newrelic.com/548C16BF.gpg | sudo apt-key add -
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive sudo apt-get install -y newrelic-php5
sudo DEBIAN_FRONTEND=noninteractive sudo newrelic-install install

