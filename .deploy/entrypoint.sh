#!/bin/sh
echo "🎬 entrypoint.sh: [$(whoami)] [PHP $(php -r 'echo phpversion();')]"

composer dump-autoload --no-interaction --optimize

echo "🎬 artisan commands"

# 💡 Group into a custom command e.g. php artisan app:on-deploy
php artisan migrate --no-interaction --force
php artisan db:seed

# command optimization
php artisan optimize:clear

/usr/bin/supervisord -c /etc/supervisord.conf
