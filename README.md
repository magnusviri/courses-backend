# Courses-backend

Generates data for [courses-frontend](https://github.com/magnusviri/courses-frontend). Written in Laravel (PHP).

## Development

### Install Vagrant and Laravel

I use [Homestead](https://laravel.com/docs/8.x/homestead#introduction).

### Install and run dev environment

	git clone https://github.com/magnusviri/courses-backend.git
	cd courses-backend
	composer update
	php artisan serve  --port=8080

### Scrape

[source webpages](https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1216/) 

Scrape 2018-2021 (1184-1218).

	php artisan scrape:now --bla=2

Scrape 1999-current.

	php artisan scrape:now --bla=1

Files will be saved to courses-backend/storage/app

## Production

Set up a Linux server with apache, php, mysql, ssl, backup, firewall, and git.

## Install node

nvm - get instructions from https://github.com/nvm-sh/nvm

	curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.37.0/install.sh | bash
	nvm install node
	npm -v

Show which versions can be installed

	nvm list-remote
	nvm install 10.9.0

## Install composer

	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
	/usr/local/bin/composer about

Composer wants unzip so install it now if needed.

## Download and configure Laravel backend

	cd ~
	git clone https://github.com/magnusviri/courses-backend.git
	cd courses-backend
    /usr/local/bin/composer install
	cp .env.example .env
	php artisan key:generate

Set DB_PASSWORD in .env

## Fix permissions and install Laravel backend

	cd ..
	sudo chown -R apache:apache courses-backend
    sudo chmod -R 755 courses-backend
	sudo chmod -R 755 courses-backend

If you're using selinux

	sudo chcon -R -t httpd_sys_content_t courses-backend/
	sudo chcon -R -t httpd_sys_rw_content_t courses-backend/storage

Finally

	sudo mv courses-backend /var/www/

### Mysql

In mysql create "courseinfo" user and "courseinfo" db then run these commands.

	cd /var/www/courses-backend
	php artisan migrate

### Apache

	nano /etc/httpd/conf.d/vhosts.conf

>     LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-agent}i\" \"%{SSL_PROTOCOL}x\" \"%{SSL_CIPHER}x\"" ssl_combined
>     
>     <VirtualHost *:80>
>         ServerAdmin webmaster@example.com
>         ServerName courseinfo.example.com
>         #ServerAlias
>         DocumentRoot "/var/www/courses-backend/public"
>         ErrorLog "/var/log/httpd/non-ssl.example.com-error_log"
>         CustomLog "/var/log/httpd/non-ssl.example.com-access_log" combined
>             RewriteEngine On
>             RewriteCond %{HTTPS} !=on
>             RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
>     </VirtualHost>
>     
>     <VirtualHost *:443>
>         ServerAdmin webmaster@example.com
>         ServerName courseinfo.example.com
>         DocumentRoot "/var/www/courses-backend/public"
>         <Directory /var/www/courses-backend>
>                     AllowOverride All
>         </Directory>
>     
>         ErrorLog "/var/log/httpd/courseinfo.example.com-ssl-error_log"
>         CustomLog "/var/log/httpd/courseinfo.example.com-ssl-access_log" ssl_combined
>             <IfModule mod_ssl.c>
>                     SSLEngine on
>                     SSLCertificateFile "/etc/pki/tls/certs/localhost.crt"
>                     SSLCertificateKeyFile "/etc/pki/tls/private/localhost.key"
>             </IfModule>
>     </VirtualHost>

Change example.com to your domain. Change SSLCertificateFile and SSLCertificateKeyFile to the location of your files.

	apachectl configtest
	sudo apachectl restart

https://stackoverflow.com/questions/30639174/how-to-set-up-file-permissions-for-laravel

## Installing the frontend

The Laravel backend app is already configured for an Angular frontend app. Just put it in place.

	cd your_angular_app_dir
	ng build --prod

Copy your_angular_app_dir/dist/courses-frontend/index.html to /var/www/course-backend/resources/views/index.html.

Copy everything else in your_angular_app_dir/dist/courses-frontend/ to /var/www/course-backend/public/.

You must retain the laravel versions of these files or else the backend api wont work.

	/var/www/course-backend/public/.htaccess
	/var/www/course-backend/public/index.php

Load your website.