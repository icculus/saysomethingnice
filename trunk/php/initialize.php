<?php

require_once 'saysomethingnice.php';

// !!! FIXME: absolutely don't let this script run on a production site!

if (isset($_SERVER['REMOTE_ADDR']))
{
    render_header();
    write_error("This isn't allowed over the web anymore.");
    render_footer();
    exit(0);
}


echo "building schema...\n";

do_dbquery("drop database if exists $dbname");
do_dbquery("create database $dbname");

close_dblink();  // force it to reselect the new database.

echo "Building admins table...\n";
do_dbquery(
    "create table admins (" .
        " id int unsigned not null auto_increment," .
        " username varchar (64) not null," .
        " password char (40) not null," .  // SHA1 hash as ASCII string
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building categories table...\n";
do_dbquery(
    "create table categories (" .
        " id int not null auto_increment," .
        " name varchar(128) not null," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building quotes table...\n";
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

echo "Building images table...\n";
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

echo "Building votes table...\n";
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

echo "Building papertrail table...\n";
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

echo "Adding 'unsorted' category...\n";
add_category('unsorted');

echo "Adding default admin...\n";
add_admin('admin', 'admin');

echo "...all done!\n\n";

echo "If there were no errors, you're good to go.\n";
echo "\n\n\n";
echo "PLEASE NOTE that there is a default login of admin/admin right now!\n".
echo " You MUST change this right now, or you have a massive security hole!\n";
echo "Go do that right now! Change it here:\n\n";
echo "     " . get_admin_url();
echo "\n\n";

?>
