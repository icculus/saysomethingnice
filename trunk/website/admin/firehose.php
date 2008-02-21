<?php

require_once '../saysomethingnice.php';

if (!valid_admin_login())
{
    admin_login_prompt();
    exit(0);
} // if

$domain = get_domain_info();
$domid = (int) $domain['id'];

$sql = 'select q.*, d.shortname as domainstr' .
       ' from quotes as q inner join domains as d on (q.domain = d.id)' .
       ' order by postdate desc limit 15';

$title = $domain['firehosename'];
$desc = $domain['firehosedesc'];
$adminurl = get_admin_url();
$rssurl = get_firehose_url();

function firehosecallback($id)
{
    global $adminurl;
    return "${adminurl}?action=edit&id=$id";
} // firehosecallback

do_rss($sql, $adminurl, $rssurl, $title, $desc, 'firehosecallback');

?>
