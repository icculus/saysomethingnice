<?php

require_once 'common.php';

function render_header($title = 'Quick, say something nice!')
{
    global $rssurl;

// !!! FIXME: need more here, I guess.
echo <<<EOF
<html>
  <head>
    <title>$title</title>
    <link rel="alternate" type="application/rss+xml" title="Speed Feed" href="${rssurl}" />
  </head>
  <body>
  <center>
    <h1>Say Something Nice</h1>
  </center>

EOF;

    write_debug('If you can read this, debugging is enabled!');
} // render_header


function render_footer()
{
// !!! FIXME: fix front page link.
// !!! FIXME: need more here, I guess.
echo <<<EOF
<hr>
<center>
  [ <a href="/saysomethingnice/">Get a quote</a> | <a href="post.php">Add a quote</a> | <a href="http://1-800-flowers.com/">Cover your ass</a> ]<br>
</center>

</body></html>

EOF;
} // render_footer

?>
