<?php
require_once 'saysomethingnice.php';

$sql = 'select * from quotes where approved=true and deleted=false order by postdate desc limit 5';
$title = 'Quick, Say Something Nice!';
$desc = 'Pulling your relationship out of the fire since 2008.';
$baseurl = get_base_url();
do_rss($sql, $baseurl, $title, $desc);
?>
