# (Lifted from Horde.org  --ryan.)
#
# If you are installing Horde for the first time, you can simply
# direct this file to mysql as STDIN:
#
# $ mysql --user=<uname> --password=<MySQL-user-password> < makedb.sql
#
# If you are upgrading from a previous version, you will need to comment
# out the the user creation steps below, as well as the schemas for any
# tables that already exist.

USE mysql;

REPLACE INTO user (host, user, password)
    VALUES (
  -- IMPORTANT: Change this!
        'HOSTNAME',
        'saysomethingnice',
  -- IMPORTANT: Change this!
        PASSWORD('PUT_A_PASSWORD_HERE')
    );

REPLACE INTO db (host, db, user, select_priv, insert_priv, update_priv,
                 delete_priv, create_priv, drop_priv)
    VALUES (
        'HOSTNAME',
        'saysomethingnice',
        'saysomethingnice',
        'Y', 'Y', 'Y', 'Y',
        'Y', 'Y'
    );

FLUSH PRIVILEGES;

DROP DATABASE IF EXISTS saysomethingnice;
CREATE DATABASE saysomethingnice;

USE saysomethingnice;

CREATE TABLE quotes (
    id int not null auto_increment,
    text mediumtext not null,
    public bool not null,
    author varchar(128) not null,
    entrydate datetime not null,
    lastedit datetime not null,
    primary key (id)
);

GRANT SELECT, INSERT, UPDATE, DELETE ON quotes TO saysomethingnice@HOSTNAME;

CREATE TABLE papertrail (
    id int not null auto_increment,
    action text not null,
    sqltext mediumtext not null,
    author varchar(128) not null,
    entrydate datetime not null,
    primary key (id)
);

GRANT SELECT, INSERT, UPDATE, DELETE ON papertrail TO saysomethingnice@HOSTNAME;

FLUSH PRIVILEGES;

# Done!
