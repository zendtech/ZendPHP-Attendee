#!/bin/bash
cd / || exit 1
echo "Setting up PhpMyAdmin..."
tar -xzf /phpmyadmin511.tar.gz
chown -R www-data:www-data /pma
echo "Setting up TestSC..."
tar -xzf /testsc-100.tar.gz
chown -R www-data:www-data /testsc
echo "Cleaning up..."
rm -rf /*.tar.gz