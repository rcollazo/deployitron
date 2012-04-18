#!/bin/bash

START=$(date +%s)

rm -Rf /tmp/pyserver.py /tmp/run.sh /scripts.tar.gz

chmod 1777 /tmp

# Starting server configuration
cd /tmp

# Get which version of RHEL we're running
VER=$(cat /etc/redhat-release | tr -dc '[0-9]' | sed '$s/.$//')

# Get the appropriate IUS and EPEL packages
if [ $VER == 5 ]; then
    EPEL="http://dl.iuscommunity.org/pub/ius/stable/Redhat/5/x86_64/epel-release-5-4.noarch.rpm"
    IUS="http://dl.iuscommunity.org/pub/ius/stable/Redhat/5/x86_64/ius-release-1.0-10.ius.el5.noarch.rpm"
else
    EPEL="http://dl.iuscommunity.org/pub/ius/stable/Redhat/6/x86_64/epel-release-6-5.noarch.rpm"
    IUS="http://dl.iuscommunity.org/pub/ius/stable/Redhat/6/x86_64/ius-release-1.0-10.ius.el6.noarch.rpm"
fi

echo "<pre>"
# get curl
echo "<h3>Installing curl ..</h3>"
yum install curl -y

# get the IUS and EPEL RPMs
echo "Downloading new packages .."
wget $EPEL
wget $IUS

# install the RPMs
echo "<h3>Installing packages ..</h3>"
rpm -Uvh /tmp/ius-release*.rpm /tmp/epel-release*.rpm

# Update all of the packages and install pwgen
echo "<h3>Updating packages ..</h3>"
yum update -y
yum install pwgen -y

# Install MySQL 5.5, Apache, PHP 5.3, APC, NTP, Postfix and sysstat (sar)
echo "<h3>Installing web and database services ..</h3>"
yum install httpd php53u php53u-pecl-apc ntp sysstat postfix mysql-server php53u-mysql -y \

# Set the services to boot automatically and start Apache, NTP, sysstat
chkconfig httpd on \
    && chkconfig mysqld on \
    && chkconfig ntpd on \
    && chkconfig sysstat on \
    && chkconfig postfix on

mv /etc/httpd/conf.d/proxy_ajp.conf /etc/httpd/conf.d/proxy_ajp.conf.disabled

service httpd start \
    && service postfix start \
    && service ntpd start \
    && service sysstat start

# Get the passwords for MysQL and the Wordpress DB User
MYSQL_ROOT_PW=$(pwgen -cns 12 1)
WP_USER_PASS=$(pwgen -cns 12 1)

# Start the MySQL service
echo "<h3>Starting MySQL ..</h3>"
service mysqld start

echo "<h3>Creating Wordpress database and setting database permissions ..</h3>"
mysql -e "create database wpdb;" \
    && mysql -e "GRANT USAGE ON *.* TO 'wpdb_user'@'localhost' IDENTIFIED BY '$WP_USER_PASS';" \
    && mysql -e "GRANT ALL PRIVILEGES ON wpdb.* to 'wpdb_user'@'localhost';"

echo "<h3>Setting MySQL root password ..</h3>"
/usr/bin/mysqladmin -u root password $MYSQL_ROOT_PW \
    && cat >> /root/.my.cnf <<EOF
[client]
user=root
password=$MYSQL_ROOT_PW
EOF

echo "<h3>Getting latest version of Worpress and configuring auth info ..</h3>"
cd /tmp/ \
    && curl -o /tmp/keys.txt https://api.wordpress.org/secret-key/1.1/salt/ \
    && curl -o /tmp/latest.tar.gz http://wordpress.org/latest.tar.gz \
    && tar xzvf latest.tar.gz && cp -r wordpress/. /var/www/html \
    && /tmp/fixwpconfig.php $WP_USER_PASS \

echo "<h3>Installing WP-Supercache and CDN-Tools (don't forget to activate!) ..</h3>"
cd /tmp/ \
    && yum install unzip -y \
    && curl -o w3-total-cache.0.9.2.4.zip http://downloads.wordpress.org/plugin/w3-total-cache.0.9.2.4.zip \
    && unzip -d /var/www/html/wp-content/plugins /tmp/w3-total-cache.0.9.2.4.zip

echo "<h3>Setting ownership and permissions of /var/www/html .. </h3>"
chown -R apache:apache /var/www/html
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

rm -Rf /tmp/*

IP=$(/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}')
# Save the iptables rules, set the service to boot on default and save them to /etc/sysconfig
echo "<h3>Configuring firewall ..</h3>"
iptables-restore /root/iptables.rules && chkconfig iptables on && service iptables save

END=$(date +%s)
DIFF=$(( $END - $START ))

echo "</pre>"
echo "All done! It took <strong>$DIFF</strong> seconds .. <br /><strong>Don't forget to activate your plugins in the Wordpress control panel!<br />"
echo "<p>You may visit your blog and finish the Wordpress installation by clicking <a target=\"_blank\" href=\"http://$IP/index.php\">here</a>."
echo "<p>You may create another install by clicking <a href=\"http://deploy.collazo.ws/index.php\">here</a>."
echo "<br />"
echo "Total install time: $DIFF seconds."