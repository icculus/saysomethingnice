<?php

require_once 'saysomethingnice.php';

// The mainline...

render_header();
if (get_input_int('id', 'Quote ID number', $id))
    render_specific_quote($id);
render_footer();

?>