<?php

require_once 'common.php';

function render_header($title = 'Quick, say something nice!')
{
// !!! FIXME: need more here, I guess.
echo <<< EOF
<html><head><title>$title</title></head><body>

EOF;
} // render_header

function render_footer()
{
    // !!! FIXME: need more here, I guess.
    echo "</body></html>\n";
} // render_footer

?>
