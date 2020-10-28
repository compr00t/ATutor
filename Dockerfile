FROM ubuntu:14.04

RUN apt-get update 
RUN DEBIAN_FRONTEND="noninteractive" apt-get -y install apache2 apache2-bin apache2-data apache2-mpm-prefork libaio1 libapache2-mod-php5 libapr1 libaprutil1 libdbd-mysql-perl libdbi-perl libhtml-template-perl libmysqlclient18 libterm-readkey-perl libwrap0 ssl-cert wget vim

RUN /etc/init.d/apache2 start

RUN echo "mysql-server-5.6 mysql-server/root_password password root" | sudo debconf-set-selections
RUN echo "mysql-server-5.6 mysql-server/root_password_again password root" | sudo debconf-set-selections
RUN apt-get -y install mysql-server-5.6 -y

RUN /etc/init.d/mysql start

RUN apt-get -y install php5-cli php5-common php5-json php5-mysql php5-readline php5-curl php5-gd

RUN /etc/init.d/apache2 stop

RUN wget https://github.com/atutor/ATutor/releases/download/atutor_2_2_1/ATutor-2.2.1.tar.gz
RUN tar -xzvf ATutor-2.2.1.tar.gz -C /var/www/html/
RUN chown -R www-data:www-data /var/www/html/ATutor

RUN update-rc.d mysql defaults

RUN mkdir /var/www/html/ATutor/content
RUN chmod 2777 /var/www/html/ATutor/content

RUN sed -i 's/display_errors = Off/display_errors = On/g' /etc/php5/apache2/php.ini
RUN sed -i 's/#general_log_file /general_log_file /g' /etc/mysql/my.cnf
RUN sed -i 's/#general_log /general_log /g' /etc/mysql/my.cnf

RUN sleep 15

RUN /etc/init.d/mysql restart

EXPOSE 80 
CMD ["apache2ctl", "-D", "FOREGROUND"]