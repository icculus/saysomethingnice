<?php

require_once 'saysomethingnice.php';

// !!! FIXME: absolutely don't let this script run on a production site!

if (isset($_SERVER['REMOTE_ADDR']))
{
    render_header();
    write_error("This isn't allowed over the web anymore.");
    render_footer();
    exit(0);
} // if

if ((!isset($argv[1])) || ($argv[1] != '--confirm'))
{
    echo "You have to run this with --confirm to do anything.\n";
    echo "...BECAUSE DOING SO DESTROYS ANY EXISTING DATABASE!!!\n";
    exit(0);
} // if


echo "Nuking any existing database (too late to go back, now!)...\n";
do_dbquery("drop database if exists $dbname");

echo "Creating new database from scratch...\n";
do_dbquery("create database $dbname");

close_dblink();  // force it to reselect the new database.

echo "Building admins table...\n";
do_dbquery(
    "create table admins (" .
        " id int unsigned not null auto_increment," .
        " username varchar (64) not null," .
        " password char (40) not null," .  // SHA1 hash as ASCII string
        " unique index username_index (username)," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building categories table...\n";
do_dbquery(
    "create table categories (" .
        " id int unsigned not null auto_increment," .
        " name varchar(128) not null," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building quotes table...\n";
do_dbquery(
    "create table quotes (" .
        " id int unsigned not null auto_increment," .
        " domain int unsigned not null," .
        " category int unsigned not null default 1," .
        " text mediumtext not null," .
        " approved bool not null default false," .
        " deleted bool not null default false," .
        " imageid int unsigned," .
        " author varchar(128) not null," .
        " ipaddr int not null," .
        " postdate datetime not null," .
        " lastedit datetime not null," .
        " index domain_index (domain)," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building images table...\n";
do_dbquery(
    "create table images (" .
        " id int unsigned not null auto_increment," .
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
        " id int unsigned not null auto_increment," .
        " ipaddr int not null," .
        " quoteid int unsigned not null," .
        " rating tinyint not null," .
        " ratedate datetime not null," .
        " lastedit datetime not null," .
        " index quoteid_index (quoteid)," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building papertrail table...\n";
do_dbquery(
    "create table papertrail (" .
        " id int unsigned not null auto_increment," .
        " action mediumtext not null," .
        " sqltext mediumtext not null," .
        " author varchar(128) not null," .
        " entrydate datetime not null," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Building domains table...\n";
do_dbquery(
    "create table domains (" .
        "id int unsigned not null auto_increment," .
        " domainname varchar(64) not null," .
        " disabled bool not null," .
        " shortname varchar(32) not null," .
        " realname varchar(128) not null," .
        " tagline varchar(128) not null," .
        " logotext varchar(128) not null," .
        " rssdesc varchar(128) not null," .
        " firehosename varchar(128) not null," .
        " firehosedesc varchar(128) not null," .
        " contactemail varchar(64) not null," .
        " twitteruser varchar(20) not null," .
        " linkurl varchar(128)," .
        " linktext varchar(128)," .
        " linkurl2 varchar(128)," .
        " linktext2 varchar(128)," .
        " adhtml mediumtext," .
        " addinstrucitons varchar(255) not null," .
        " unique index domainname_index (domainname)," .
        " primary key (id)" .
    " ) character set utf8"
);

echo "Adding default domain...\n";
do_dbinsert(
    "insert into domains" .
    " (domainname, shortname, realname, tagline, logotext, rssdesc, firehosename, firehosedesc, contactemail)" .
    " values (" .
    " 'quicksaysomethingnice.com'," .
    " 'default'," .
    " 'Quick, Say Something Nice!'," .
    " 'Flowers? Candy? Jewelry? Relationship advice? No time for that!'," .
    " 'Fixing your romantic relationships since 2008.'," .
    " 'Pulling your relationships out of the fire since 2008.'," .
    " 'Say Something Nice Admin Firehose'," .
    " 'Look here to filter out people looking for a booty call.'," .
    " 'contact@quicksaysomethingnice.com'" .
    ")"
);

echo "Adding obama domain...\n";
do_dbinsert(
    "insert into domains" .
    " (domainname, shortname, realname, tagline, logotext, rssdesc, firehosename, firehosedesc, contactemail, linkurl, linktext)" .
    " values (" .
    " 'obama.quicksaysomethingnice.com'," .
    " 'obama'," .
    " 'Quick, Say Something Nice to Barack Obama!'," .
    " 'Send some hope to the candidate of Hope!'," .
    " 'Cheering on Barack Obama since 2008.'," .
    " 'Cheering on Barack Obama since 2008.'," .
    " 'Obama qssn Admin Firehose'," .
    " 'Look here to filter out Hillary wonks.'," .
    " 'contact@quicksaysomethingnice.com'," .
    " 'http://barackobama.com/'," .
    " 'Go to Barack Obama's website.'" .
     ")"
);

echo "Adding clinton domain...\n";
do_dbinsert(
    "insert into domains" .
    " (domainname, shortname, realname, tagline, logotext, rssdesc, firehosename, firehosedesc, contactemail, linkurl, linktext)" .
    " values (" .
    " 'clinton.quicksaysomethingnice.com'," .
    " 'clinton'," .
    " 'Quick, Say Something Nice to Hillary Clinton!'," .
    " 'Pass on good feelings to Hillary!'," .
    " 'Cheering on Hillary Clinton since 2008.'," .
    " 'Cheering on Hillary Clinton since 2008.'," .
    " 'Clinton qssn Admin Firehose'," .
    " 'Look here to filter out Obama cult members.'," .
    " 'contact@quicksaysomethingnice.com'," .
    " 'http://hillaryclinton.com/'," .
    " 'Go to Hillary Clinton's website.'" .
    ")"
);

echo "Adding mccain domain...\n";
do_dbinsert(
    "insert into domains" .
    " (domainname, shortname, realname, tagline, logotext, rssdesc, firehosename, firehosedesc, contactemail, linkurl, linktext)" .
    " values (" .
    " 'mccain.quicksaysomethingnice.com'," .
    " 'mccain'," .
    " 'Quick, Say Something Nice to John McCain!'," .
    " 'Send some kind words to a true American hero!'," .
    " 'Cheering on John McCain since 2008.'," .
    " 'Cheering on John McCain since 2008.'," .
    " 'McCain qssn Admin Firehose'," .
    " 'Look here to filter out Huckabee weenies.'," .
    " 'contact@quicksaysomethingnice.com'," .
    " 'http://johnmccain.com/'," .
    " 'Go to John McCain's website.'" .
     ")"
);

echo "Adding 'unsorted' category...\n";
add_category('unsorted');

echo "Adding default admin...\n";
add_admin('admin', 'admin');

echo "...all done!\n\n";

echo "If there were no errors, you're good to go.\n";
echo "\n\n\n";
echo "PLEASE NOTE that there is a default login of admin/admin right now!\n";
echo " You MUST change this right now, or you have a massive security hole!\n";
echo "Go do that right now! Change it here:\n\n";
echo "     " . get_admin_url();
echo "\n\n";

?>
