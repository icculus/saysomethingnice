<?php

// Common functions for this website.

require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';

function get_base_url()
{
    global $baseurl;
    return $baseurl;
} // get_base_url


function get_quote_url($id)
{
    $baseurl = get_base_url();
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${baseurl}quote.php?id=${id}";
} // get_quote_url


function get_email_url($id)
{
    $baseurl = get_base_url();
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${baseurl}email.php?id=${id}";
} // get_email_url


function get_rate_url($id, $good)
{
    $baseurl = get_base_url();
    $id = (int) $id;   // just in case it came from a URL or something.
    $thumbs = $good ? "true" : "false";
    return "${baseurl}rate.php?id=${id}&thumbs=${thumbs}";
} // get_rate_url


function get_img_url($id)
{
    $baseurl = get_base_url();
    $id = (int) $id;   // just in case it came from a URL or something.
    return "${baseurl}image.php?id=${id}";
} // get_img_url


function get_rss_url()
{
    $baseurl = get_base_url();
    return "${baseurl}rss.php";
} // get_rss_url


function get_post_url()
{
    $baseurl = get_base_url();
    return "${baseurl}post.php";
} // get_post_url


function get_admin_url()
{
    $baseurl = get_base_url();
    return "${baseurl}admin/admin.php";
} // get_admin_url


function get_firehose_url()
{
    $baseurl = get_base_url();
    return "${baseurl}admin/firehose.php";
} // get_firehose_url


function render_quote_to_string($text, $id = NULL, $imageid = NULL)
{
    $retval = '';
    $htmltext = escapehtml($text);
    $retval .= "<center>\n";
    $retval .= "\"${htmltext}\"\n";

    if ( (isset($imageid)) && (((int) $imageid) > 0) )
    {
        $img_url = get_img_url($imageid);
        $retval .= "<br/><img src='$img_url' />\n";
    } // if

    if (isset($id))
    {
        $quote_url = get_quote_url($id);
        $email_url = get_email_url($id);
        $good_url = get_rate_url($id, true);
        $bad_url = get_rate_url($id, false);
        $retval .= "<p><font size='-3'>[" .
                   " <a href='$quote_url'>link</a> |" .
                   " <a href='$email_url'>email</a> |" .
                   " <a href='$good_url'>thumbs up</a> |" .
                   " <a href='$bad_url'>thumbs down</a> ]" .
                   " </font></p>\n";
    } // if

    $retval .= "</center>\n";

    return $retval;
} // render_quote_to_string


function render_quote($text, $id = NULL, $imageid = NULL)
{
    echo render_quote_to_string($text, $id, $imageid);
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
            render_quote($row['text'], $row['id'], $row['imageid']);
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


function add_quote($quote, $author, $ipaddr)
{
    $sqlquote = db_escape_string($quote);
    $sqlauthor = db_escape_string($author);
    $ipaddr = (int) $ipaddr;

    $sql = "insert into quotes (text, author, ipaddr, postdate, lastedit)" .
           " values ($sqlquote, $sqlauthor, $ipaddr, NOW(), NOW())";

    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
        update_papertrail("Quote added", $sql);
    return $inserted;
} // add_quote


function update_quote($id, $quote=NULL, $author=NULL, $ipaddr=NULL)
{
    $id = (int) $id;

    $updstr = '';
    if (isset($quote))
        $updstr .= ", text=" . db_escape_string($quote);
    if (isset($author))
        $updstr .= ", author=" . db_escape_string($author);
    if (isset($ipaddr))
        $updstr .= ", ipaddr=" . ((int) $ipaddr);

    if ($updstr == '')
        return true;

    $sql = "update quotes set lastedit=NOW()$updstr where id=$id";
    $updated = (do_dbupdate($sql, 1) == 1);
    if ($updated)
        update_papertrail("Updated quote", $sql);
    return $updated;
} // add_quote


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
           " where id=$voteid and quoteid=$quoteid and ipaddr=$ipaddr" .
           " and rating<>$rating";

    $updated = (do_dbupdate($sql, 1) == 1);
    if ($updated)
    {
        $ratestr = ($rating > 0) ? 'up' : 'down';
        $ipstr = long2ip($ipaddr);
        update_papertrail("$ipstr changed vote for quote #${quoteid} to ${ratestr}", $sql);
    } // if
    return $updated;
} // update_rating


function get_admin_id($username)
{
    $sqlname = db_escape_string($username);
    $sql = "select id from admins where username=$sqlname limit 1";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;

    $row = db_fetch_array($query);
    if ($row == false)  // no matching login?
        return false;

    return (int) $row['id'];
} // get_admin_id


function add_admin($username, $password)
{
    if (get_admin_id($username) !== false)
    {
        $username = escapehtml($username);
        write_error("Admin '$username' already exists.");
        return false;
    } // if

    $sqlname = db_escape_string($username);
    $sqlpass = SHA1($password);

    $sql = "insert into admins (username, password) values ($sqlname, '$sqlpass')";
    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
        update_papertrail("Admin '$username' added", $sql);
    return $inserted;
} // add_admin


function delete_admin($username)
{
    $id = get_admin_id($username);
    if ($id === false)
    {
        $username = escapehtml($username);
        write_error("No such admin '$username'");
        return false;
    } // if

    $sql = "delete from admins where id=$id limit 1";
    $deleted = (do_dbdelete($sql) == 1);
    if ($deleted)
    {
        update_papertrail("Admin '$username' deleted", $sql);
        // Move any admin stuff to a default admin here, if necessary in the future.
    } // if
    return $deleted;
} // delete_admin


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
    if ($row != false)  // category already exists?
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


function add_image($bin, $mimetype, $ipaddr, $id=-1)
{
    $sqlbin = db_escape_string($bin);
    $sqlmime = db_escape_string($mimetype);
    $ipaddr = (int) $ipaddr;
    $id = (int) $id;

    $sql = "insert into images (data, mimetype, ipaddr, postdate) values" .
           " ($sqlbin, $sqlmime, $ipaddr, NOW())";
    $inserted = (do_dbinsert($sql) == 1);

    $sql2 = '';
    $updated = true;
    if ($id > 0)
    {
        $sql2 = "update quotes set imageid=LAST_INSERT_ID(), lastedit=NOW() where id=$id";
        $updated = do_dbupdate($sql2);

        // We don't delete the old image (if any) from the the database
        //  when this replaces it...in theory, we could have multiple quotes
        //  with the same image, or maybe we'll build a UI to list out
        //  available images so you can (re)assign them between quotes. For
        //  now, though, they're just orphaned data in the table, but at least
        //  we can pull it out manually if we need it later.
    } // if

    // Do papertrail down here, since the papertrail insert will kill
    //  LAST_INSERT_ID(), and we want these to remain chronological.

    if ($inserted)
        update_papertrail("Image added", $sql);

    if (($id > 0) && ($updated))
        update_papertrail("Updated quote with new image", $sql2);

    return $inserted && $updated;
} // add_image


function valid_admin_login_internal()
{
    get_login($user, $pass);
    if (!isset($user))
        return false;
    if (!isset($pass))
        return false;

    $user = db_escape_string($user);
    $pass = SHA1($pass);

    $sql = "select id from admins where username=$user and password='$pass'";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;

    $row = db_fetch_array($query);
    if ($row == false)  // no matching login?
    {
        if (!empty($pass))
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


function do_rss($sql, $baseurl, $basetitle, $basedesc)
{
    // DATE_RSS doesn't appear to be defined on Dreamhost if you are using
    //  mod_php instead of PHP CGI, so the Admin Firehose screws up pubdates.
    $daterss = 'D, d M Y H:i:s T';

    $query = do_dbquery($sql);
    if ($query == false)
    {
        header('HTTP/1.0 500 Internal Server Error');
        render_header();
        write_error('query failed');
        render_footer();
        return;
    } // if

    header('Content-Type: text/xml; charset=UTF-8');

    $rowcount = db_num_rows($query);
    $newestentrytime = current_sql_datetime();
    if ($rowcount > 0)
    {
        $row = db_fetch_array($query);
        if ($row != false)
            $newestentrytime = $row['postdate'];
        db_reset_array($query);
    } // if

    $pubdate = date($daterss, sql_datetime_to_unix_timestamp($newestentrytime));

    $items = '';
    $digestitems = '';
    while ( ($row = db_fetch_array($query)) != false )
    {
        $url = get_quote_url($row['id']);
        $text = escapehtml($row['text']);
        $desc = escapehtml(render_quote_to_string($row['text'], $row['id'], $row['imageid']));
        $postdate = date($daterss, sql_datetime_to_unix_timestamp($row['postdate']));
        $items .= "<item rdf:about=\"$url\"><title>\"${text}\"</title><pubDate>${postdate}</pubDate>" .
                  "<description>${desc}</description>" .
                  "<link>${url}</link></item>\n";
        $digestitems .= "<rdf:li rdf:resource=\"${url}\" />\n";
    } // while
    db_free_result($query);

    $rssurl = get_rss_url();

    // stupid question mark endtag screws up PHP, even in strings and comments!
    $xmltag = '<' . '?' . 'xml version="1.0" encoding="UTF-8"' . '?' . '>';
    echo <<<EOF
$xmltag
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns="http://purl.org/rss/1.0/">

  <channel rdf:about="$rssurl">
    <title>$basetitle</title>
    <link>$baseurl</link>
    <description>$basedesc</description>
    <pubDate>${pubdate}</pubDate>
    <items>
      <rdf:Seq>
        $digestitems
      </rdf:Seq>
    </items>
  </channel>

  $items

</rdf:RDF>

EOF;

} // do_rss

?>
