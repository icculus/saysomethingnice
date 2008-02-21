<?php

require_once 'saysomethingnice.php';

header('Content-Type: text/plain;charset=UTF-8');

// The mainline...
// This is ALL this does...it doesn't output the <html> tags or anything;
//  the widget handles that.

$domain = get_domain_info();
$domid = (int) $domain['id'];

// !!! FIXME: abstract this.
// !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
$sql = 'select * from quotes' .
       ' where domain=$domid and approved=true and deleted=false' .
       ' order by rand() limit 1';

$query = do_dbquery($sql);
if ($query == false)
    echo 'query failed';
else if ( ($row = db_fetch_array($query)) == false )
    echo 'No quote at the moment, apparently.';
else
    echo $row['text'];
?>

