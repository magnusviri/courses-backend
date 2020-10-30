# Courses-backend

Generates data for [courses-frontend](https://github.com/magnusviri/courses-frontend). Written in Laravel (PHP).

Right now all this does is scrape the [source webpages](https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1216/) and save JSON files.

## Install and run dev environment

	git clone https://github.com/magnusviri/courses-backend.git
	cd courses-backend
	composer update
	php artisan serve  --port=8080

## Scrape

Scrape 2018-2021 (1184-1218).

	php artisan scrape:now --bla=2

Scrape 1999-current.

	php artisan scrape:now --bla=1

Files will be saved to courses-backend/storage/app
