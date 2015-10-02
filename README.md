#The Things Network Gateway Status Monitor

## Installation and setup

 - clone this repository to your local machine or server
 - setup Phalcon framework (see https://phalconphp.com/en/download)
 - run 'composer install' in the project root (https://getcomposer.org/download/ to install composer)
 - run 'sudo aptitude install -y beanstalkd' to install beanstalkd
 - create the database by importing the 'config/mysql/ttnm-create-db.sql' file
 - create a mysql user with access to this database
 - copy the 'config/config-sample.php' to 'config/config.php'
 - enter the mysql user details in the config file
 - set the other parameters in the config file as you see fit
 - setup a crontab ('crontab -e') to receive gateway status updates, for example:
 >\*/5 * * * * php /var/www/ttnm/scripts/UpdateGateways.php
 >\* * * * * php /var/www/ttnm/scripts/GatewayStatusUpdate.php
 
 UpdateGateways.php discovers new gateways and sets their status
 GatewayStatusUpdate.php send status updates to Slack

 - setup your webserver (Apache, Nginx, etc) to point to the 'public' directory of this repository, and you should be good to go!