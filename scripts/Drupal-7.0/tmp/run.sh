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
curl -o /tmp/ius-release-1.0-6.ius.el5.noarch.rpm http://dl.iuscommunity.org/pub/ius/stable/Redhat/5/x86_64/ius-release-1.0-6.ius.el5.noarch.rpm 

# install the RPMs
echo "<h3>Installing packages ..</h3>"
rpm -Uvh /tmp/ius-release*.rpm /tmp/epel-release*.rpm

# Update all of the packages and install pwgen
echo "<h3>Updating packages ..</h3>"
yum update -y
yum install pwgen -y

# Install MySQL 5.1, Apache, PHP 5.3, APC, NTP, Postfix and sysstat (sar)
echo "<h3>Installing web and database services ..</h3>"
yum install httpd php53u php53u-pecl-apc ntp sysstat postfix -y \
    && yum install mysql55-server -y \
    && yum install php53u-mysql php53u-gd php53u-xml php53u-mbstring -y

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

# Get the passwords for MysQL and the Drupal DB User
MYSQL_ROOT_PW=`pwgen -cns 12 1`
DRUPAL_USER_PASS=`pwgen -cns 12 1`

# Start the MySQL service
echo "<h3>Starting MySQL ..</h3>"
service mysqld start

echo "<h3>Creating Drupal database and setting database permissions ..</h3>"
mysql -e "create database drupaldb;" \
    && mysql -e "GRANT USAGE ON *.* TO 'drupaldb_user'@'localhost' IDENTIFIED BY '$DRUPAL_USER_PASS';" \
    && mysql -e "GRANT ALL PRIVILEGES ON drupaldb.* to 'drupaldb_user'@'localhost';"

echo "<h3>Setting MySQL root password and saving info in /root/.my.cnf ..</h3>"
/usr/bin/mysqladmin -u root password $MYSQL_ROOT_PW \
    && cat >> /root/.my.cnf <<EOF
[client]
user=root
password=$MYSQL_ROOT_PW
EOF

echo "<h3>Saving Drupal DB username and password in /root/drupal-db.txt ..</h3>"
cat >> /root/drupal-db.txt <<EOF
user=drupaldb_user
password=$DRUPAL_USER_PASS
database=drupaldb
EOF

echo "<h3>Getting Drupal 7.0 and extracting ..</h3>"
cd /tmp/ \
    && curl -o /tmp/drupal-7.0.tar.gz http://ftp.drupal.org/files/projects/drupal-7.0.tar.gz \
    && tar xzvf drupal-7.0.tar.gz && mv drupal-7.0/* drupal-7.0/.htaccess /var/www/html

echo "<h3>Setting ownership and permissions of /var/www/html .. </h3>"
chown -R apache:apache /var/www/html
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

rm -Rf /tmp/*

IP=`/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'`
# Save the iptables rules, set the service to boot on default and save them to /etc/sysconfig
echo "<h3>Configuring firewall ..</h3>"
iptables-restore /root/iptables.rules && chkconfig iptables on && service iptables save

echo "</pre>"
echo "All done!<br />"
echo "<p>You must visit your blog and finish the Drupal installation by clicking <a target=\"_blank\" href=\"http://$IP/index.php\">here</a>."
echo "<p>Your Drupal database information is located in /root/drupal-db.txt"
echo "<p>You may create another install by clicking <a href=\"http://deploy.collazo.ws/index.php\">here</a>."
echo "<br />"
