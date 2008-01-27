<?php

// Common functions for this website.

// !!! FIXME: change these.
$baseurl = 'http://centralserver/saysomethingnice/';
$rssurl = 'http://centralserver/saysomethingnice/rss.php';
$quoteurl = 'http://centralserver/saysomethingnice/quote.php';
$emailurl = 'http://centralserver/saysomethingnice/email.php';
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


function get_rate_url($id, $good)
{
    global $rateurl;
    $id = (int) $id;   // just in case it came from a URL or something.
    $thumbs = $good ? "up" : "down";
    return "${rateurl}?id=${id}&thumbs=${thumbs}";
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
        $good_url = get_rate_url($id, true);
        $bad_url = get_rate_url($id, false);
        echo "<p><font size='-3'>[" .
             " <a href='$quote_url'>link</a> |" .
             " <a href='$email_url'>email</a> |" .
             " <a href='$good_url'>thumbs up</a> |" .
             " <a href='$bad_url'>thumbs down</a> ]" .
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


function calculate_quote_rating($quoteid, &$rating, &$votes)
{
    // !!! FIXME: this is all probably wickedly inefficient once you get some
    // !!! FIXME:  data into the tables...
    $rating = 0;
    $votes = 0;
    $quoteid = (int) $quoteid;

    $sql = "select rating from votes where quoteid=$quoteid";
    $query = do_dbquery($sql);
    if ($query == false)
        write_error('query failed');
    else
    {
        while ( ($row = db_fetch_array($query)) != false )
        {
            $rating += $row['rating'];
            $votes++;
        } // while
    } // else
} // calculate_quote_rating


function add_rating($quoteid, $ipaddr, $rating)
{
    $quoteid = (int) $quoteid;
    $ipaddr = (int) $ipaddr;
    $rating = (int) $rating;

    if ($rating == 0)
        return true;  // don't do anything here.

    $sql = "insert into votes (ipaddr, quoteid, rating, ratedate, lastedit)" .
           " values ($ipaddr, $quoteid, $rating, NOW(), NOW())";
    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
    {
        $ratestr = ($rating > 0) ? 'up' : 'down';
        $ipstr = long2ip($ipaddr);
        update_papertrail("Vote quote #${quoteid} ${ratestr} from $ipstr", $sql);
    } // if
    return $inserted;
} // add_rating


function update_rating($voteid, $ipaddr, $quoteid, $rating)
{
    $voteid = (int) $voteid;
    $ipaddr = (int) $ipaddr;
    $quoteid = (int) $quoteid;
    $rating = (int) $rating;

    $sql = "update votes set rating=$rating, lastedit=NOW()" .
           " where voteid=$voteid and quoteid=$quoteid and ipaddr=$ipaddr" .
           " and rating<>$rating";

    $updated = (do_dbupdate($sql, 1) == 1);
    if ($updated)
        update_papertrail("Vote $voteid changed to $rating", $sql);
    return $updated;
} // update_rating


function add_admin($username, $password)
{
    $sqlname = db_escape_string($username);
    $sqlpass = SHA1($password);

    $sql = "insert into admins (username, password) values ($sqlname, $sqlpass)";
    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
        update_papertrail("Admin '$username' added", $sql);
    return $inserted;
} // add_admin


function change_admin_password($user, $oldpass, $newpass)
{
    $user = db_escape_string($user);
    $oldpass = SHA1($oldpass);
    $pass = SHA1($newpass);

    $sql = "update admins set password='$pass' where username=$user and password='$oldpass'";
    $updated = (do_dbupdate($sql, 1) == 1);  // someone changed it from under you?
    if ($updated)
        update_papertrail("Admin '$user' password changed", $sql);
    return $updated;
} // change_admin_password


function add_category($name)
{
    $sqlname = db_escape_string($name);
    $sql = "select id from categories where name=$sqlname limit 1";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;

    $inserted = false;
    $row = db_fetch_array($query);
    if ($row != false)  // no matching login?
    {
        $id = $row['id'];
        write_error("Category '$name' already exists (id #$id)");
    } // if
    else
    {
        $sql = "insert into categories (name) values ($sqlname)";
        $inserted = (do_dbinsert($sql) == 1);
        if ($inserted)
            update_papertrail("Category '$name' added", $sql);
    } // if
    return $inserted;
} // add_category


function delete_category($id)
{
    if ($id == 1)
    {
        write_error("You can't delete the 'unsorted' category!");
        return false;
    } // if

    $sqlid = (int) $id;
    $sql = "delete from categories where id=$sqlid";
    $deleted = (do_dbdelete($sql) == 1);
    if ($deleted)
    {
        update_papertrail("Category '$id' deleted", $sql);
        $sql = "update quotes set category=1 where category=$sqlid";
        $moved = do_dbupdate($sql, -1);
        if ($moved)
            update_papertrail("Moved $moved quotes to unsorted category", $sql, "id=$sqlid");
    } // if
    return $deleted;
} // add_category


function valid_admin_login_internal()
{
    if (!isset($_SERVER['PHP_AUTH_USER']))
        return false;
    $user = $_SERVER['PHP_AUTH_USER'];
    if (!isset($_SERVER['PHP_AUTH_PW']))
        return false;
    $pass = $_SERVER['PHP_AUTH_PW'];

    $user = db_escape_string($user);
    $pass = "'" . SHA1($pass) . "'";

    $sql = "select id from admins where username=$user and password=$pass";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;

    $row = db_fetch_array($query);
    if ($row == false)  // no matching login?
    {
        sleep(3);  // discourage brute-force attacks.
        return false;
    } // if

    return true;  // we've got a match.
} // valid_admin_login_internal


function valid_admin_login()
{
    static $already_checked = false;  // don't hit database multiple times.
    static $retval = false;
    if (!$already_checked)
    {
        $already_checked = true;
        $retval = valid_admin_login_internal();
    } // if
    return $retval;
} // valid_admin_login


function admin_login_prompt()
{
    $realm = "saysomethingnice admin";
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    header('HTTP/1.0 401 Unauthorized');
    render_header();
    write_error('This page requires a valid admin login.');
    render_footer();
    exit(0);
} // admin_login_prompt

?>