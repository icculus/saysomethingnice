<?php

require_once '../saysomethingnice.php';

if (!valid_admin_login())
{
    admin_login_prompt();
    exit(0);
} // if

$sql = 'select * from quotes order by postdate desc limit 15';
$title = 'Say Something Nice Admin Firehose';
$desc = 'Look here to filter out people looking for a booty call.';
$baseurl = get_admin_url();
$rssurl = get_firehose_url();

function firehosecallback($id)
{
    global $baseurl;
    return "${baseurl}?action=edit&id=$id";
} // firehosecallback

do_rss($sql, $baseurl, $rssurl, $title, $desc, 'firehosecallback');

?>
