#!/bin/sh
# Konfiguracijski zadaci za tutum/lamp image

echo short_open_tag = On > /etc/php5/apache2/conf.d/30_short_open_tag.ini
chown www-data /home/zamger

# Patchujemo skriptu create_mysql_admin_user kako bi importovala bazu iz dumpa
head -n -1 /create_mysql_admin_user.sh > /tmp.sh
echo "# Import data from zamger dump" >> /tmp.sh
echo "echo \"=> Importing data from zamger dump... \(may take a few minutes\)\"" >> /tmp.sh
echo mysql -uroot -e \"CREATE DATABASE zamger\" >> /tmp.sh
echo "mysql -uroot zamger < /app/db/schema.sql" >> /tmp.sh
echo "mysql -uroot zamger < /app/db/seed.sql" >> /tmp.sh
echo >> /tmp.sh
echo mysqladmin -uroot shutdown >> /tmp.sh
mv /tmp.sh /create_mysql_admin_user.sh
chmod 755 /create_mysql_admin_user.sh

# Nastavak normalnog starta
/run.sh