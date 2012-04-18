#!/bin/bash

rm -Rf /tmp/pyserver.py /tmp/run.sh /scripts.tar.gz

chmod 1777 /tmp

# Starting server configuration
cd /tmp

echo "<pre>"
# get curl
echo "<h3>Installing curl ..</h3>"
yum install curl -y

# get the IUS and EPEL RPMs
echo "Downloading new packages .."
curl -o /tmp/epel-release-1-1.ius.el5.noarch.rpm http://dl.iuscommunity.org/pub/ius/stable/Redhat/5/x86_64/epel-release-1-1.ius.el5.noarch.rpm
curl -o /tmp/ius-release-1-2.ius.el5.noarch.rpm http://dl.iuscommunity.org/pub/ius/stable/Redhat/5/x86_64/ius-release-1-4.ius.el5.noarch.rpm

# install the RPMs
echo "<h3>Installing packages ..</h3>"
rpm -Uvh /tmp/ius-release*.rpm /tmp/epel-release*.rpm

# Update all of the packages and install pwgen
echo "<h3>Updating packages ..</h3>"
yum update -y
yum install pwgen -y

# Install MySQL 5.1, Apache, PHP 5.2, APC, NTP, Postfix and sysstat (sar)
echo "<h3>Installing web and database services ..</h3>"
yum install httpd php52 php52-pecl-apc php52-xml php52-mcrypt php52-hash php52-gd mod_ssl ntp sysstat postfix -y \
    && yum install mysql51-server -y \
    && yum install php52-pdo php52-mysql -y \

# Set the services to boot automatically and start Apache, NTP, sysstat
chkconfig httpd on \
    && chkconfig mysqld on \
    && chkconfig ntpd on \
    && chkconfig sysstat on \
    && chkconfig postfix on \
    && mv /etc/httpd/conf.d/proxy_ajp.conf /etc/httpd/conf.d/proxy_ajp.conf.disabled \
    && service httpd start \
    && service postfix start \
    && service ntpd start \
    && service sysstat start

# Get the passwords for MysQL and the Wordpress DB User
MYSQL_ROOT_PW=`pwgen -cns 12 1`
MAGENTO_USER_PASS=`pwgen -cns 12 1`

# Start the MySQL service
echo "<h3>Starting MySQL ..</h3>"
mkdir -p /var/log/mysql
chown -R mysql:mysql /var/log/mysql
chmod 770 /var/log/mysql
service mysqld start
if [ "$?" -ne "0" ]; then
    chown -R mysql:mysql /var/log/mysql
    chmod 770 /var/log/mysql
    service mysqld start
    if [ "$?" -ne "0" ]; then
        chown -R mysql:mysql /var/log/mysql
        chmod 770 /var/log/mysql
        service mysqld start
	if [ "$?" -ne "0" ]; then
            echo "There was a problem starting MySQL. Trying again"
            sleep 120
        fi
    fi
fi

echo "<h3>Creating Magento database and setting database permissions ..</h3>"
mysql -e "create database magentodb;" \
    && mysql -e "GRANT USAGE ON *.* TO 'magento_user'@'localhost' IDENTIFIED BY '$MAGENTO_USER_PASS';" \
    && mysql -e "GRANT ALL PRIVILEGES ON magentodb.* to 'magento_user'@'localhost';"

echo "<h3>Setting MySQL root password ..</h3>"
/usr/bin/mysqladmin -u root password $MYSQL_ROOT_PW \
    && cat >> /root/.my.cnf <<EOF
[client]
user=root
password=$MYSQL_ROOT_PW
EOF

echo "<h3>Getting Magento 1.3.2.1 and configuring auth info ..</h3>"
cd /tmp/ \
    && curl -o /tmp/magento-1.3.2.1.tar.gz http://www.magentocommerce.com/downloads/assets/1.3.2.1/magento-1.3.2.1.tar.gz \
    && tar xzvf magento-1.3.2.1.tar.gz && cp -R magento/. /var/www/html/ \
    && /tmp/fixmagentoconfig.php $MAGENTO_USER_PASS

echo "<h3>Setting ownership and permissions of Magento files in /var/www/html .. </h3>"
chown -R apache:apache /var/www/html
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

rm -Rf /tmp/*

IP=`/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'`
# Save the iptables rules, set the service to boot on default and save them to /etc/sysconfig
echo "<h3>Configuring firewall ..</h3>"
iptables-restore /root/iptables.rules && chkconfig iptables on && service iptables save

echo "</pre>"
echo "All done!<br /><br />"
echo "<p>You may visit your site and finish the Magento installation by clicking <a target=\"_blank\" href=\"http://$IP/index.php/manage\">here</a>. The default login information is username: admin password: 123123 - <strong>CHANGE THIS IMMEDIATELY!</strong></p>"
echo "<p>You may create another install by clicking <a href=\"http://deploy.collazo.ws/index.php\">here</a>.</p>"
echo "<br />"
