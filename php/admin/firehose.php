<?php

require_once '../saysomethingnice.php';

if (!valid_admin_login())
{
    admin_login_prompt();
    exit(0);
} // if

do_rss('select * from quotes order by postdate desc limit 15');

?>
