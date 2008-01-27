<?php

require_once 'saysomethingnice.php';

// The mainline...
// This is ALL this does...it doesn't output the <html> tags or anything;
//  the widget handles that.

// !!! FIXME: abstract this.
// !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
$sql = 'select * from quotes where approved=true and deleted=false order by rand() limit 1;';
$query = do_dbquery($sql);
if ($query == false)
    echo 'query failed';
else if ( ($row = db_fetch_array($query)) == false )
    echo 'No quote at the moment, apparently.';
else
    echo escapehtml($row['text']);
?>

