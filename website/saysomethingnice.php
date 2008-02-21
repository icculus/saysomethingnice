<?php

// Common functions for this website.

require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';

function get_domain_info()
{
    static $domain = NULL;
    if (isset($domain))
        return $domain;

    $host = $_SERVER['SERVER_NAME'];
    if (empty($host))
        $host = 'quicksaysomethingnice.com'; // for php from command line.
    else if (substr($host, 0, 4) == 'www.')
        $host = substr($host, 4);

    $sqlhost = db_escape_string($host);
    $sql = "select * from domains where domainname=$sqlhost limit 1";
    $query = do_dbquery($sql, NULL, true);

    $failout = false;
    if ( ($query == false) || ( ($domain = db_fetch_array($query)) == false ) )
        $failout = true;
    else if ($domain['disabled'] != 0)
        $failout = true;

    if ($failout)
    {
        header('HTTP/1.0 503 Server Error');
        header('Content-Type: text/plain;charset=utf-8');
        header('Cache-Control: no-cache');
        echo("\n\n\nDomain is disabled/misconfigured, or database is down. Try again later.\n\n\n");

        global $enable_debug;
        if ($enable_debug)
        {
            $err = mysql_error();
            echo("\n\n(debug data...)\n\n");
            echo("SQL statement was: $sql\n\n");
            echo("mysql_error() reports: {$err}\n\n");
        } // if

        exit(0);  // just bail now.
    } // if

    return $domain;
} // get_domain_info


function get_base_url()
{
    static $retval = NULL;
    if (!isset($retval))
    {
        $domain = get_domain_info();
        $retval = "http://${domain['domainname']}/";
    } // if
    return $retval;
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


function get_css_url()
{
    $baseurl = get_base_url();
    return "${baseurl}style.css";
} // get_css_url


function get_static_imgdir_url()
{
    $baseurl = get_base_url();
    return "${baseurl}img/";
} // get_imgdir_url


function get_widget_url()
{
    $baseurl = get_base_url();
    return "${baseurl}downloads/SaySomethingNice.wdgt.zip";
} // get_widget_url


function get_contact_url()
{
    $domain = get_domain_info();
    $addr = escapehtml($domain['contactemail']);
    return "mailto:$addr";
} // get_contact_url


function render_quote_to_string($text, $id = NULL, $imageid = NULL, $randomized=false)
{
    $htmltext = escapehtml($text);

    $adstartignore = "\n<!-- google_ad_section_start(weight=ignore) -->\n";
    $adstartnoignore = "\n<!-- google_ad_section_start -->\n";
    $adstart = ($randomized) ? $adstartignore : $adstartnoignore;
    $adend = "\n<!-- google_ad_section_end -->\n";

    $imghtml = '';
    if ( (isset($imageid)) && (((int) $imageid) > 0) )
    {
        $imghtml = get_img_url($imageid);
        $imghtml .= "<br/><img src='$imghtml' />\n";
    } // if

    $linkhtml = '';
    $thumbshtml = '';
    if (isset($id))
    {
        $quote_url = get_quote_url($id);
        //$email_url = get_email_url($id);
        $good_url = get_rate_url($id, true);
        $bad_url = get_rate_url($id, false);
        $imgurl = get_static_imgdir_url();

        $linkhtml = ' ' .
                    "<a href='$quote_url'>" .
                      "<img src='${imgurl}chainlinkicon_1.png'" .
                      " style='border-style: none; vertical-align: middle'" .
                      " alt='link' title='permalink to this quote' />" .
                    "</a>";

        $thumbshtml = "<p>" .
                        "<a href='$good_url'>" .
                          "<img src='${imgurl}thumbup.png'" .
                          " style='border-style: none; vertical-align: middle'" .
                          " alt='thumbs up!' title='Vote this quote UP!' />" .
                        "</a>" .
                        "&nbsp;&nbsp;&nbsp;&nbsp;" .
                        "<a href='$bad_url'>" .
                          "<img src='${imgurl}thumbdown.png'" .
                          " style='border-style: none; vertical-align: middle'" .
                          " alt='thumbs down!' title='Vote this quote DOWN!' />" .
                        "</a>" .
                      "</p>";
    } // if

    return "<center>" .
             "<div class='box' style='width: 30%'>" .
               "<div class='boxtop'><div></div></div>" .
               "<div class='boxcontent'>" .
                   $adstart .
                   "\"${htmltext}\"" .
                   $adend .
                   $adstartignore .
                   $linkhtml .
                   $adend .
               "</div>" .
               "<div class='boxbottom'><div></div></div>" .
             "</div>" .
             $adstartignore .
             $thumbshtml .
             $adend .
           "</center>";
} // render_quote_to_string


function render_quote($text, $id = NULL, $imageid = NULL, $randomized=false)
{
    echo render_quote_to_string($text, $id, $imageid, $randomized);
} // render_quote


function select_and_render_quote($sql, $randomized=false)
{
    $query = do_dbquery($sql);
    if ($query == false)
        write_error('query failed');
    else
    {
        if ( ($row = db_fetch_array($query)) == false )
            write_error("No quote at the moment, apparently. Maybe we deleted or haven't approved it yet?");
        else
            render_quote($row['text'], $row['id'], $row['imageid'], $randomized);
        db_free_result($query);
    } // else
} // select_and_render_quote


function render_specific_quote($id)
{
    $domain = get_domain_info();
    $domid = (int) $domain['id'];
    $id = (int) $id;
    $sql = "select * from quotes where id=$id and domain=$domid" .
           " and approved=true and deleted=false limit 1;";
    select_and_render_quote($sql, false);
} // render_specific_quote


function render_random_quote()
{
    $domain = get_domain_info();
    $domid = (int) $domain['id'];
    // !!! FIXME: 'order by rand() limit 1' isn't efficient as the size of the table grows!
    $sql = "select * from quotes where domain=$domid and approved=true and deleted=false order by rand() limit 1";
    select_and_render_quote($sql, true);
} // render_random_quote


function add_quote($quote, $author, $ipaddr)
{
    // Trim quotes from submitted quotes (we add them ourselves later).
    // I have a feeling there's a more efficient way to do this...
    $len = strlen($quote);
    if ($len > 1)
    {
        $firstch = substr($quote, 0, 1);
        $lastch = substr($quote, $len-1, 1);
        if (($firstch == $lastch) && (($firstch == '"') || ($firstch == "'")))
            $quote = substr($quote, 1, $len-2);
    } // if

    if (empty($quote))
        return false;

    $sqlquote = db_escape_string($quote);
    $sqlauthor = db_escape_string($author);
    $ipaddr = (int) $ipaddr;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];
    $sql = "insert into quotes (domain, text, author, ipaddr, postdate, lastedit)" .
           " values ($domid, $sqlquote, $sqlauthor, $ipaddr, NOW(), NOW())";

    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
        update_papertrail("Quote added", $sql);
    return $inserted;
} // add_quote


function update_quote($id, $quote=NULL, $author=NULL, $ipaddr=NULL, $approved=NULL, $deleted=NULL, $domainid=NULL)
{
    $id = (int) $id;

    $updstr = '';
    if (isset($quote))
        $updstr .= ", text=" . db_escape_string($quote);
    if (isset($author))
        $updstr .= ", author=" . db_escape_string($author);
    if (isset($ipaddr))
        $updstr .= ", ipaddr=" . ((int) $ipaddr);
    if (isset($approved))
        $updstr .= ", approved=" . (($approved) ? 'true' : 'false');
    if (isset($deleted))
        $updstr .= ", deleted=" . (($deleted) ? 'true' : 'false');
    if (isset($domainid))
        $updstr .= ", domain=" . ((int) $domainid);

    if ($updstr == '')
        return true;

    $sql = "update quotes set lastedit=NOW()$updstr where id=$id";
    $updated = (do_dbupdate($sql, 1) == 1);
    if ($updated)
        update_papertrail("Updated quote", $sql);
    return $updated;
} // update_quote


function calculate_quote_rating($quoteid, &$rating, &$votes, &$positive, &$negative)
{
    // !!! FIXME: this is all probably wickedly inefficient once you get some
    // !!! FIXME:  data into the tables...
    $rating = 0;
    $votes = 0;
    $positive = 0;
    $negative = 0;
    $quoteid = (int) $quoteid;

    $sql = "select rating from votes where quoteid=$quoteid";
    $query = do_dbquery($sql);
    if ($query == false)
        write_error('query failed');
    else
    {
        while ( ($row = db_fetch_array($query)) != false )
        {
            $val = $row['rating'];
            $rating += $val;
            if ($val >= 0)
                $positive += $val;
            else
                $negative -= $val;
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


function do_rss($sql, $baseurl, $rssurl, $basetitle, $basedesc, $callback)
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

    header('Content-Type: text/xml;charset=UTF-8');

    $rowcount = db_num_rows($query);
    $newestentrytime = current_sql_datetime();
    if ($rowcount > 0)
    {
        // This assumes that they are sorted by postdate (which sorting
        //  by id should also accomplish, faster...)
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
        $domainstr = '';
        if (isset($row['domainstr']))
        {
            $domainstr = escapehtml($row['domainstr']);
            $domainstr = "($domainstr) ";
        } // if

        $url = $callback($row['id']);
        $urlenc = escapehtml($url);
        $text = escapehtml($row['text']);
        $desc = escapehtml(render_quote_to_string($row['text'], $row['id'], $row['imageid'], false));
        $postdate = date($daterss, sql_datetime_to_unix_timestamp($row['postdate']));
        $items .= "<item rdf:about=\"$urlenc\"><title>$domainstr\"${text}\"</title><pubDate>${postdate}</pubDate>" .
                  "<description>${desc}</description>" .
                  "<link>${urlenc}</link></item>\n";
        $digestitems .= "<rdf:li rdf:resource=\"${urlenc}\" />\n";
    } // while
    db_free_result($query);

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
