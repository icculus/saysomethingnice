<?php
require_once 'saysomethingnice.php';

$domain = get_domain_info();
$domid = (int) $domain['id'];

$sql = 'select * from quotes' .
       " where domain=$domid and approved=true and deleted=false" .
       ' order by id desc limit 5';

$title = $domain['realname'];
$desc = $domain['rssdesc'];
$baseurl = get_base_url();
$rssurl = get_rss_url();
do_rss($sql, $baseurl, $rssurl, $title, $desc, 'get_quote_url');
?>
