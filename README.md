## Novagrade Wordpress Site


### Getting Up and Running
1. Clone this repo
1. Make sure that NFS file sharing for Vagrant is configured correctly. [Read](https://www.vagrantup.com/docs/synced-folders/nfs.html)
1. You shouldn't need to do anything on OS X but it will need your `sudo` password to modify `/etc/exports`
1. Run `vagrant up`
1. Run `vagrant ssh` to login to the virtual server
1. Install PHP FPM and Nginx: `sudo apt-get install -y vim libapr1 libaprutil1 libdbd-mysql-perl libdbi-perl libnet-daemon-perl libplrpc-perl libpq5 build-essential mysql-server nginx php7.0-fpm php7.0-mbstring php7.0-xml php7.0-soap php7.0-ssl php7.0-mysql php7.0-curl php7.0-gd htop`
1. Change the php-fpm pool configuration: edit the file `/etc/php/7.0/fpm/pool.d/www.conf`
1. Change line 34 to `listen = /run/php/php7.0-fpm.sock`
1. Change line 47 to `listen.mode = 0666`
1. Pull down the Nginx configuration file: `sudo wget -O /etc/nginx/sites-available/novagrade.conf https://gist.githubusercontent.com/adamrunner/94b495a993c20a5e264bd1c4df81c396/raw/05b0162d89fcf6f9b91a7d8708f37d3ebc0dd484/default.conf`
1. Create the database `sudo mysql -e "create database novagrade_development; create user 'novagrade'@'localhost' identified by password('password'); flush privileges; grant all on *.* to 'novagrade'@'localhost';"`
1. Download the database `wget -O ~/novagrade_development.sql https://www.dropbox.com/s/1bcefdclk77gein/novagrade_development.sql?dl=0#`
1. Import the database `mysql -u novagrade -ppassword novagrade_development < novagrade_development.sql`
1. Download the `gen_cert.sh` script `wget https://gist.github.com/adamrunner/285746ca0f22b0f2e10192427e0b703c/raw/23ec7544d0377aea3df06e4e9a684935c68bd397/gen_cert.sh`
1. Run `./gen_cert.sh novagrade.dev`
1. Move the generated certificate: `sudo mv novagrade.dev.crt /etc/ssl/certs/novagrade.dev.crt`
1. Move the generated key: `sudo mv novagrade.dev.key /etc/ssl/prviate/novagrade.dev.key`
1. Add `novagrade.dev 192.168.33.10` to `/etc/hosts` on your local OS X machine
1. Modify your `wp-config-local.php` file in the root of the site. You'll need to fill in the database credentials and name here.
1. Restart nginx and php-fpm


### Deploying to dev.novagrade.com

Generally, some of your changes will happen in the database - because WordPress.

Here are some scripts that will automate the majority of the work for you, they assume that your development environment is running locally on Vagrant, and that your local database name is `novagrade_development`. It also assumes that you have an SSH key copied to novagrade.com and you have a SSH Host entry so you can simply type `ssh novagrade` to connect.

They also assume that your `.my.cnf` file is configured so that you can log directly into `mysql` or `mysqldump` without using usernames and passwords.

1. `./upload_database [restore]` - This command will take the latest snapshot from your Vagrant machine and upload it to dev.novagrade.com, if you pass `restore` to the script, it will also restore it to the development database.
2. If you'd like to restore later: `ssh novagrade 'bash -s' < restore` - This command will restore the database on the development server with the copy you just uploaded.