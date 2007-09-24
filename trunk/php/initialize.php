<?php

require_once 'saysomethingnice.php';

// !!! FIXME: absolutely don't let this script run on a production site!

render_header();

echo "building schema...<br>\n";

do_dbquery("drop database if exists $dbname;");
do_dbquery("create database $dbname;");

do_dbquery(
    "create table categories (" .
        " id int not null auto_increment," .
        " name varchar(128) not null," .
        " primary key (id)" .
    " );"
);

do_dbquery(
    "create table quotes (" .
        " id int not null auto_increment," .
        " category int not null," .
        " text mediumtext not null," .
        " public bool not null," .
        " author varchar(128) not null," .
        " entrydate datetime not null," .
        " lastedit datetime not null," .
        " primary key (id)" .
    " );"
);

do_dbquery(
    "create table papertrail (" .
        " id int not null auto_increment," .
        " action text not null," .
        " sqltext mediumtext not null," .
        " author varchar(128) not null," .
        " entrydate datetime not null," .
        " primary key (id)" .
    " );"
);

echo "Inserting some initial rows...<br>\n";
do_dbinsert("insert into categories (name) values ('unsorted');");

echo "...all done!<br>\n";

render_footer();

?>