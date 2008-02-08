<?php

require_once 'common.php';

function render_header($title=NULL, $headextras='', $showads=true)
{
    $rssurl = get_rss_url();
    $posturl = get_post_url();
    $cssurl = get_css_url();

    if ($title == NULL)
        $title = 'Quick, say something nice!';

    $advertisements = $showads ? get_advertisements() : '';

// !!! FIXME: need more here, I guess.
echo <<<EOF
<html>
  <head>$headextras
    <title>$title</title>
    <link rel="alternate" type="application/rss+xml" title="Speed Feed" href="${rssurl}" />
    <link rel="stylesheet" type="text/css" href="${cssurl}" />
  </head>
  <body>
  <center>
    $advertisements
    <h1>Quick, Say Something Nice!</h1>
  </center>

EOF;

    write_debug('If you can read this, debugging is enabled!');
} // render_header


function render_footer()
{
    $baseurl = get_base_url();
    $posturl = get_post_url();

// !!! FIXME: need more here, I guess.
echo <<<EOF
<hr>
<center>
  [ <a href="${baseurl}">Get a quote</a> |
    <a href="${posturl}">Add a quote</a> |
    <a href="${baseurl}downloads/SaySomethingNice.wdgt.zip">Get Mac Widget</a>
  ]<br>
</center>

</body></html>

EOF;
} // render_footer

?>
