<?php

require_once 'saysomethingnice.php';

if (!valid_admin_login())
{
    admin_login_prompt();
    exit(0);
} // if

$enable_debug = true;  // always for this page.


// !!! FIXME: absolutely don't let this script run on a production site!

render_header();

echo "building schema...<br>\n";

do_dbquery("drop database if exists $dbname");
do_dbquery("create database $dbname");

close_dblink();  // force it to reselect the new database.

do_dbquery(
    "create table admins (" .
        " id int unsigned not null auto_increment," .
        " username varchar (64) not null," .
        " password char (40) not null," .  // SHA1 hash as ASCII string
        " primary key (id)" .
    " ) character set utf8"
);

do_dbquery(
    "create table categories (" .
        " id int not null auto_increment," .
        " name varchar(128) not null," .
        " primary key (id)" .
    " ) character set utf8"
);

do_dbquery(
    "create table quotes (" .
        " id int not null auto_increment," .
        " category int not null default 1," .
        " text mediumtext not null," .
        " approved bool not null default false," .
        " deleted bool not null default false," .
        " imageid int," .
        " author varchar(128) not null," .
        " ipaddr int not null," .
        " postdate datetime not null," .
        " lastedit datetime not null," .
        " primary key (id)" .
    " ) character set utf8"
);

do_dbquery(
    "create table images (" .
        " id int not null auto_increment," .
        " data mediumblob not null," .
        " mimetype varchar(64) not null," .
        " ipaddr int not null," .
        " postdate datetime not null," .
        " primary key (id)" .
    " ) character set utf8"
);

do_dbquery(
    "create table votes (" .
        " id int not null auto_increment," .
        " ipaddr int not null," .
        " quoteid int not null," .
        " rating tinyint not null," .
        " ratedate datetime not null," .
        " lastedit datetime not null," .
        " primary key (id)" .
    " ) character set utf8"
);

do_dbquery(
    "create table papertrail (" .
        " id int not null auto_increment," .
        " action text not null," .
        " sqltext mediumtext not null," .
        " author varchar(128) not null," .
        " entrydate datetime not null," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Inserting some initial rows...<br>\n";
add_category('unsorted');
add_admin('icculus', 'aaa');  // This is not a permanent password.  :)
add_admin('carrie', 'bbb');  // This is not a permanent password.  :)
add_quote("I love your cats.", 'carrie@icculus.org', ip2long('127.0.0.1'));
add_quote("mumumumumumumu.", 'icculus@icculus.org', ip2long('127.0.0.1'));

echo "...all done!<br>\n";

render_footer();

?>