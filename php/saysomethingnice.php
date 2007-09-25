<?php

// Common functions for this website.

// !!! FIXME: change these.
$baseurl = 'http://centralserver/saysomethingnice/';
$rssurl = 'http://centralserver/saysomethingnice/rss.php';
$quoteurl = 'http://centralserver/saysomethingnice/quote.php';
$emailurl = 'http://centralserver/saysomethingnice/email.php';
$inappropriateurl = 'http://centralserver/saysomethingnice/inappropriate.php';
$rateurl = 'http://centralserver/saysomethingnice/rate.php';
$posturl = 'http://centralserver/saysomethingnice/post.php';

require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';


function get_quote_url($id)
{
    global $quoteurl;
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${quoteurl}?id=${id}";
} // get_quote_url


function get_email_url($id)
{
    global $emailurl;
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${emailurl}?id=${id}";
} // get_email_url


function get_inappropriate_url($id)
{
    global $inappropriateurl;
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${inappropriateurl}?id=${id}";
} // get_inappropriate_url


function get_rate_url($id)
{
    global $rateurl;
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${rateurl}?id=${id}";
} // get_rate_url


function render_quote($text, $id = NULL)
{
    $htmltext = htmlentities($text, ENT_QUOTES);
    echo "<center>\n";
    echo "\"${htmltext}\"\n";

    if (isset($id))
    {
        $quote_url = get_quote_url($id);
        $email_url = get_email_url($id);
        $inappropriate_url = get_inappropriate_url($id);
        $rate_url = get_rate_url($id);
        echo "<p><font size='-3'>[" .
             " <a href='$quote_url'>link</a> |" .
             " <a href='$email_url'>email</a> |" .
             " <a href='$inappropriate_url'>inappropriate</a> ]" .
             " <a href='$rate_url'>rate</a> ]" .
             " </font></p>\n";
    } // if

    echo "</center>\n";
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
            render_quote($row['text'], $row['id']);
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
        update_papertrail("Category '$name' added", $sql);
    return $inserted;
} // add_category


function delete_category($id)
{
    if ($id == 1)
    {
        write_error("You can't delete the 'unsorted' category!");
        return false;
    } // if

    $sqlid = db_escape_string($id);
    $sqlname = db_escape_string($name);
    $sql = "delete from categories where id=$sqlid;";
    $deleted = (do_dbdelete($sql) == 1);
    if ($deleted)
    {
        update_papertrail("Category '$id' deleted", $sql);
        $sql = "update quotes set category=1 where category=$sqlid;";
        $moved = do_dbupdate($sql, -1);
        if ($moved)
            update_papertrail("Moved $moved quotes to unsorted category", $sql, "id=$sqlid");
    } // if
    return $deleted;
} // add_category

?>