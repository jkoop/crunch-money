#!/bin/sh

cd /var/www

ln -s /storage storage
touch /storage/database.sqlite || exit 1

if [ -z $APP_KEY ]; then
	echo -en '\n\n\n\tYou need to add this to your environment:\n\t'
	echo APP_KEY=base64:$(head -c 32 /dev/urandom | base64)
	echo -e '\n\n'
	exit 1
fi

cp -v /storage/database.sqlite /storage/database_$(date +%Y%m%d-%H%M%S).sqlite
php artisan migrate --force
php artisan app:init

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf