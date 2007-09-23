<?php

require_once 'saysomethingnice.php';

function render_quote($sql)
{
    $query = do_dbquery($sql);
    if ($query == false)
        write_error('query failed');
    else
    {
        if ( ($row = db_fetch_array($query)) == false )
            write_error('No quote at the moment, apparently.');
        else
        {
            $text = htmlentities($row['text'], ENT_QUOTES);
            echo "<center>\"${text}\"</center>\n";
        } // else
        db_free_result($query);
    } // else
} // render_quote


function render_specific_quote($id)
{
    render_quote('select * from quotes where id=$id and public=true limit 1;');
} // render_random_quote


function render_random_quote()
{
    // !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
    render_quote('select * from quotes where public=true order by rand() limit 1;');
} // render_random_quote


// The mainline...
render_header();

if (get_input_int('id', 'Quote ID number', $id, -1))
    render_specific_quote($id);
else
    render_random_quote();

render_footer();

?>