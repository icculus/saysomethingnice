<?php

require_once 'saysomethingnice.php';

// The mainline...

render_header();
if (get_input_int('id', 'Quote ID number', $id))
{
    // !!! FIXME
    write_error("Ryan hasn't implemented this yet. But here's the quote you wanted emailed...");
    render_specific_quote($id);
} // if
render_footer();

?>