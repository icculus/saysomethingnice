<?php
require_once 'saysomethingnice.php';
do_rss('select * from quotes where approved=true and deleted=false order by postdate desc limit 5');
?>
