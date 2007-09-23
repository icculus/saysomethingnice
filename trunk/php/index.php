<?php

require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';

function render_random_quote()
{
    // !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
    $query = do_dbquery('select * from quotes where public=true order by rand() limit 1;');
    if ($query == false)
        write_error('query failed');
    else
    {
        if ( ($row = db_fetch_array($query)) == false )
            write_error('No quotes at the moment, apparently.');
        else
        {
            $text = htmlescape($row['text']);
            echo "<center>\"${text}\"</center>\n";
        } // else
        db_free_result($query);
    } // else
} // render_random_quote

// The mainline...
render_header();
render_random_quote();
render_footer();

?>
