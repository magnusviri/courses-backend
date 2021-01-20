# Courses-backend

Generates data for [courses-frontend](https://github.com/magnusviri/courses-frontend). Written in Laravel (PHP).

## Development

### Install Vagrant and Laravel

I use [Homestead](https://laravel.com/docs/8.x/homestead#introduction).

### Install and run dev environment

	git clone https://github.com/magnusviri/courses-backend.git
	cd courses-backend
	composer update
	php artisan serve --port=8080

## Set it up for Production

Set up a Linux server with apache, php, mysql, ssl, backup, firewall, and git.

### Install composer

	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
	/usr/local/bin/composer about

Composer needs unzip so install it now if needed.

### Download and configure Laravel backend

	cd ~
	git clone https://github.com/magnusviri/courses-backend.git
	cd courses-backend
    /usr/local/bin/composer install
	cp .env.example .env
	php artisan key:generate

Set DB_PASSWORD in .env

### Fix permissions and install the Laravel backend

	cd ..
	sudo chown -R apache:apache courses-backend
    sudo chmod -R 755 courses-backend

If you're using selinux

	sudo chcon -R -t httpd_sys_content_t courses-backend/
	sudo chcon -R -t httpd_sys_rw_content_t courses-backend/storage

Finally

	sudo mv courses-backend /var/www/

### Mysql

Create "courseinfo" db and user.

    mysql -u root -p
    CREATE DATABASE courseinfo;
    CREATE USER 'courseinfo'@'localhost' IDENTIFIED BY 'secret';
    GRANT ALL PRIVILEGES ON courseinfo.* TO 'courseinfo'@'localhost';
    FLUSH PRIVILEGES;
    quit

Then run these commands.

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

The Laravel backend app is already configured for the Angular [frontend](https://github.com/magnusviri/courses-frontend) app. Just put it in place with these instructions.

	cd your_angular_app_dir
	ng build --prod

Copy your_angular_app_dir/dist/courses-frontend/index.html to /var/www/course-backend/resources/views/index.html.

Copy everything else in your_angular_app_dir/dist/courses-frontend/ to /var/www/course-backend/public/.

You must retain the laravel versions of these files or else the backend api wont work.

	/var/www/course-backend/public/.htaccess
	/var/www/course-backend/public/index.php

Load your website.

## Working with the Data

### Scrape the Data

Usage:

    php artisan scrape:now
    php artisan scrape:now --start=2020
    php artisan scrape:now --end=2020
    php artisan scrape:now --start=2020 --end=2020
    php artisan scrape:now --sub=BIOL
    php artisan scrape:now --nocache
    php artisan scrape:now --nosave
    php artisan scrape:now --verbose

Files will be saved to courses-backend/storage/app. If a file exists then the data wont be scraped from the source webpages. The --nocache option will override this and it will scrape the source webpages.

`--nosave` will cause the script to skip saving to MySQL.

[Source webpages](https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1214/).

### Scraper Maintenance

The likelihood that the source webpages will change in the future is pretty much 100%. The file that performs the scraping is [app/Console/Commands/ScrapeNow.php](https://github.com/magnusviri/courses-backend/blob/main/app/Console/Commands/ScrapeNow.php). It is heavily commented with the intention of making it easier to maintain and update if (when) the source webpages change.

Symfony 5.2 [DomCrawler Component](https://symfony.com/doc/5.2/components/dom_crawler.html) is the scraper. The very first action it performs is loading the main page.

    $crawler = $client->request('GET', $url);
    $semester_data = $crawler->filter('.class-info')->each(function($item, $i) {
        return ScrapeNow::scrape_class_list_page($item, $i);
    });

The scrape_class_list_page method then goes through the HTML and pulls out relevant info. For example, the very first thing it grabs is the catalog number.

    $catalog_number_text = $item->filter('.class-info h3 > a')->text(); // This value is only used to scrape the sections page

The [filter](https://symfony.com/doc/5.2/components/dom_crawler.html#node-filtering) method is what does all the heavy lifting. It takes a CSS selector for the parameter. If you don't know [how CSS selectors work](https://css-tricks.com/how-css-selectors-work/) you'll need to learn that first. Then read about the filter method. Also read up on [accessing node values](https://symfony.com/doc/5.2/components/dom_crawler.html#accessing-node-values).

The easy way to getting the CSS selector to be used for the filter value is to use Chrome/Chromium (Brave) and right click on the item to be scraped and then right click on the item in the source code view and select "Copy" -> "Copy selector". That gets you the selector path that the filter can use find the text. It's actually really easy doing this if you understand CSS selectors.

3 wepbages are scraped, class_list.html, sections.html, and description.html. They are scraped by scrape_class_list_page, scrape_sections_page, and scrape_description_page respectively. Each course has an associative array that stores all of the scraped data. At the end of the scrape_class_list_page method it copies the course data into a new course2 array. This is because the keys/field names that are stored for the database are completely different from the keys used to scrape. I'm not sure if this is a mistake, but it does save space (especially the json).

Because some keys are dynamically pulled from the webpage, there is some level of unpredictability. So I look for known keys (at the time I made this) and I throw an exception if it finds an unknown key. I also throw an exception when copying the data from the first course array to course2 if the data isn't unset from course. That's to make sure d

There is some duplicate data on each page. It is ignored (it's stored with 'Unused' at the start to indicate it's ignored). I haven't found any contradictory data except for the value stored in 'Unused Section Course Name' (I'm ignoring this inconsistency). And then some courses don't have the Class Number set. Trying to protect against this inconsistency is actually why this the scraper code is complex. I've made it as simple as I can figure out. But the side effect is that there's just a lot of code.

### Table Structure

There are 3 main tables.

- courses
- instructors 
- attrs (note, "Attributes" is used in the frameworks so I had to avoid that name).

The relationships are many-to-many between courses and instructors and between courses and attrs. These tables hold those linking relationships.

- attr_course
- course_instructor

Everything is named according to the [JsonAPI](https://jsonapi.org/) conventions so that it only needs minimal configuration. See the [Laravel JsonAPI docs](https://laravel-json-api.readthedocs.io/en/latest/) for more information.

The data is stored in the database by [app/Console/Commands/ScrapeNow.php](https://github.com/magnusviri/courses-backend/blob/main/app/Console/Commands/ScrapeNow.php) and it uses [Laravel Eloquent](https://laravel.com/docs/8.x/eloquent), specifically the [firstOrNew](https://laravel.com/docs/8.x/eloquent#retrieving-or-creating-models) method.

### Resetting the Data

If the database gets messed up and you need to reimport the whole thing do this.

	php artisan migrate:rollback
	php artisan migrate
    php artisan scrape:now
