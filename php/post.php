<?php

require_once 'saysomethingnice.php';

function process_possible_submission()
{
    if (!has_input('submitting'))
        return;  // no submission, we'll just show the submission form later.

    write_debug('Got a submission!');
} // process_possible_submission


function render_submission_ui()
{
    $form = get_form_tag();

echo <<< EOF
    $form
    Whisper sweet nothings: <input type='text' name='quote'><br>
    (optional) email address: <input type='text' name='author'><br>
    <input type='hidden' name='submitting' value='true'>
    <input type='submit' name='submit' value='Go!'>
    </form>

EOF;
} // render_submission_ui


// The mainline...
render_header();
process_possible_submission();
render_submission_ui();
render_footer();

?>
