# wp-deploy

> A set of command-line tools for simple deployment of Wordpress installs. Can be used for automated deployment.

At this point, this is just a personal deploy script.  I might make it into something more generic in the future.

All it does is sync the upload folder, and the database (including search/replace of things like URLs).

## Install

```
curl -s http://getcomposer.org/installer | php
php composer.phar require cullylarson/wp-deploy
```

## Usage

To use, create a .env file (see `samples/sample.env`), and then run the `wp-push` or `wp-pull` commands.

## Configuration

Configuration is done using environment variables.  This turned out to be not the best solution, so it will probably
change in the future.  You can set the environment variables yourself (e.g put them in a shell script and run the
script before using wp-push or wp-pull), or put them in a .env file in the current directory (which will be
loaded automatically).

The following environment variables are used:

* __SOURCE_WP_UPLOADS__ Path to the uploads folder on the source machine.  Must end in a backslash.
* __SOURCE_TMP__ A folder to store temporary files on the source machine.
* __SOURCE_MYSQL_HOST__ The mysql host name on the source machine.
* __SOURCE_MYSQL_USERNAME__ 
* __SOURCE_MYSQL_PASSWORD__ 
* __SOURCE_MYSQL_NAME__ 
* __SOURCE_MYSQL_PORT__
* __SOURCE_SSH_HOST__ If the source machine is not local (see **SOURCE_IS_LOCAL**), this is the SSH hostname of the source machine. This script uses SSH-RSA trusted keys to authenticate the SSH connection.  So, you can't use this script with a password.
* __SOURCE_SSH_USERNAME__ SSH username on source machine.
* __SOURCE_SRDB__ If you're doing search/replace on the source machine (i.e. when using `wp-pull`), then this is the path to srdb.cli.php (part of (interconnectit/Search-Replace-DB)[https://github.com/interconnectit/Search-Replace-DB]). This command is used to the search/replace in the database.
* __SOURCE_IS_LOCAL__ Set to 1 or 0.  Indicates whether the source machine is the local machine.

* __SOURCE_WP_SEARCH1__ If doing a search/replace in the database on wp-pull, this is the first search term (e.g. "www.somesourcedomain.com").
It will replaced with the value of **SOURCE_WP_REPLACE1**.  You can provide as many of these as you want (e.g. SOURCE_WP_SEARCH2, SOURCE_WP_SEARCH3).
They just always need a corresponding REPLACE.
* __SOURCE_WP_REPLACE1__ If doing a search/replace in the database on wp-pull, this is the first replace term (e.g. "www.thenewdomain.com").
It will replace the value of **SOURCE_WP_SEARCH1**.  You can provide as many of these as you want (e.g. SOURCE_WP_REPLACE2, SOURCE_WP_REPLACE3).
They just always need a corresponding SEARCH.

* __DEST_WP_UPLOADS__ Path to the uploads folder on the destination machine.  Must end in a backslash.
* __DEST_TMP__ A folder to store temporary files on the destination machine.
* __DEST_MYSQL_HOST__ The mysql host name on the destination machine.
* __DEST_MYSQL_USERNAME__ 
* __DEST_MYSQL_PASSWORD__ 
* __DEST_MYSQL_NAME__ 
* __DEST_MYSQL_PORT__ 
* __DEST_SSH_HOST__ If the destination machine is not local (see **DEST_IS_LOCAL**), this is the SSH hostname of the destination machine. This script uses SSH-RSA trusted keys to authenticate the SSH connection.  So, you can't use this script with a password.
* __DEST_SSH_USERNAME__ SSH username on destination machine.
* __DEST_SRDB__ If you're doing search/replace on the destination machine (i.e. when using `wp-push`), then this is the path to srdb.cli.php (part of (interconnectit/Search-Replace-DB)[https://github.com/interconnectit/Search-Replace-DB]). This command is used to the search/replace in the database.
* __DEST_IS_LOCAL__ Set to 1 or 0.  Indicates whether the destination machine is the local machine.

* __DEST_WP_SEARCH1__ If doing a search/replace in the database on wp-push, this is the first search term (e.g. "www.somesourcedomain.com").
It will replaced with the value of **DEST_WP_REPLACE1**.  You can provide as many of these as you want (e.g. DEST_WP_SEARCH2, DEST_WP_SEARCH3).
They just always need a corresponding REPLACE.
* __DEST_WP_REPLACE1__ If doing a search/replace in the database on wp-push, this is the first replace term (e.g. "www.thenewdomain.com").
It will replace the value of **DEST_WP_SEARCH1**.  You can provide as many of these as you want (e.g. DEST_WP_REPLACE2, DEST_WP_REPLACE3).
They just always need a corresponding SEARCH.

* __LOCAL_TMP__ If doing a remote-to-remote sync (e.g. between two remote machines), this this is the path to a local folder to store temporary files.
This is required because files from the remote source will be transferred locally before moving them to the remote destination.

## wp-push

This command is used to push changes from the source to the destination.

```
% php vendor/bin/wp-push
```

## wp-pull

This command is used to pull changes from the destination to the source.

```
% php vendor/bin/wp-pull
```

## SSH Authentication

The script using ssh-rsa trusted key authentication.  So, your local machine's key needs to be in the
`authorized_keys` file on the source and destination, if they aren't local.  The script won't authenticate
using passwords.

If you're getting and authentication error, try running this:

```
% eval `ssh-agent -s` && ssh-add
```