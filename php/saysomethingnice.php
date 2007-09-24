<?php

// Common functions for this website.

// !!! FIXME: change these.
$baseurl = 'http://centralserver/saysomethingnice/';
$rssurl = 'http://centralserver/saysomethingnice/rss.php';
$quoteurl = 'http://centralserver/saysomethingnice/quote.php';
$posturl = 'http://centralserver/saysomethingnice/post.php';

require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';

function render_quote($text)
{
    $htmltext = htmlentities($text, ENT_QUOTES);
    echo "<center>\"${htmltext}\"</center>\n";
} // render_quote


function select_and_render_quote($sql)
{
    $query = do_dbquery($sql);
    if ($query == false)
        write_error('query failed');
    else
    {
        if ( ($row = db_fetch_array($query)) == false )
            write_error('No quote at the moment, apparently.');
        else
            render_quote($row['text']);
        db_free_result($query);
    } // else
} // select_and_render_quote


function render_specific_quote($id)
{
    $sql = "select * from quotes where id=$id and approved=true and deleted=false limit 1;";
    select_and_render_quote($sql);
} // render_random_quote


function render_random_quote()
{
    // !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
    $sql = 'select * from quotes where approved=true and deleted=false order by rand() limit 1;';
    select_and_render_quote($sql);
} // render_random_quote


function add_category($name)
{
    $sqlname = db_escape_string($name);
    $sql = "insert into categories (name) values ('$sqlname');";
    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
        update_papertrail("Category '$name' added", $sql, NULL);
    return $inserted;
} // add_category

?>