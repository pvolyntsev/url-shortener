Test application "URL shortener"
================================

This appication was made according to test task http://www.xiag.ch/testtask/ and 
recomendations http://blog.xiag.ru/2012/10/reminder-for-candidates.html


Application was created from the scratch during 2 evenings almost without COPY&PASTE (except models/AlphaId.php and models/Form/Validate/Url.php)
* MVC patten (see AppController.php, AppForm.php, AppModel.php, AppView.php)
* ORM-like database access (see AppDbConnection.php, ApModel.php)
* MVVM pattern (see AppForm.php)
* Configurable components
* AJAX on native javascript


All PHP classes are shown at [base classes](https://raw.github.com/pvolyntsev/url-shortener/master/docs/main_classes.png) and [application classes](https://raw.github.com/pvolyntsev/url-shortener/master/docs/classes.png)

DEMO:
-----
You can try demo at http://url-sh.copist.ru/


REQUIREMENTS:
-------------
PHP 5.3, MYSQL 5.5, NGINX, PHP-FPM


INSTALLATION:
-------------

-- 1. install software (for debian/ubuntu)

    $ sudo apt-get install php5 php5-mysql mysql-client-core-5.5 mysql-server
    
    You can use NGINX or APACHE web server (not tested with Apache)

    $ sudo nginx php5-fpm

-- 2. may need some manual configuration ...



-- 3. create new mysql database
see http://www.mysql.ru/docs/man/CREATE_DATABASE.html

for example create database named 'url'

    $ mysql -u{adminLogin} -p{adminPassword}

mysql shell

    > CREATE DATABASE IF NOT EXISTS `url` DEFAULT CHARACTER SET utf8;
    > QUIT;



-- 4. create new user
see http://www.mysql.ru/docs/man/Adding_users.html

for example create user named 'url_rw' with password 'user-password'

    $ mysql -u{adminLogin} -p{adminPassword}

mysql shell

    > USE `url`;
    > GRANT ALL PRIVILEGES ON *.* TO url_rw@localhost IDENTIFIED BY 'user-password';
    > QUIT;



-- 5. prepare directory for web application

    $ sudo mkdir -p /var/www/url.com
    $ sudo chown -R nginx:www-data /var/www/url.com



-- 6. download web application (need account at github.com) into prepared directory /var/www/url

    $ git clone git@github.com:pvolyntsev/url-shortener.git /var/www/url



-- 7. register web applicatio in nginx

    $ sudo ln -s /var/www/url.com/app/config/url.com.conf /etc/nginx/sites-enabled/
    $ sudo /etc/init.d/nginx reload



-- 8. load database dump from /var/www/url.com/dump_url.sql

for example

    $ mysql -u{adminLogin} -p{adminPassword} {databaseName} < /var/www/url.com/dump_url.sql



-- 9. add this line into /etc/hosts

    127.0.0.1 dev-url.com url.com

if you use virtual machine to run application, add the same line into 'hosts' file at your host machine, with other IP address



-- 10. open http://dev-url.com/ in browser


CONFIGURING
-------------
If database name, database user login or password differ from those are in this manual, you need manually change options in /var/www/url.com/app/config/app.php


    ...
   	// Configuration for database component
   	'db' => array(
   		'class' => 'AppPdoConnection',
   		'connectionString' => 'mysql:host=127.0.0.1;dbname=url',
   		'username' => 'url_rw',
   		'password' => 'user-password',
   	),
    ...


After install on production you need to set your domain in nginx configuration file /var/www/url.com/app/config/url.com.conf

    server {
        ....
        server_name {YOURDOMAIN} dev-url.com url.com;
    ...

Then reload configuration

    $ sudo /etc/init.d/nginx reload
