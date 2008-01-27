<?php

require_once 'saysomethingnice.php';

function process_possible_submission()
{
    if (!has_input('submitting'))
        return;  // no submission, we'll just show the submission form later.

    if (!get_input_string('quote', 'Quote text', $quote)) return;
    if (!get_input_string('author', 'Email address', $author, '', true)) return;

    write_debug('Got an apparently non-bogus submission!');

    $sqlquote = db_escape_string($quote);
    $sqlauthor = db_escape_string($author);
    $ipaddr = ip2long($_SERVER['REMOTE_ADDR']);

    $sql = "insert into quotes (text, author, ipaddr, postdate, lastedit)" .
           " values ($sqlquote, $sqlauthor, $ipaddr, NOW(), NOW())";

    $inserted = (do_dbinsert($sql) == 1);
    if ($inserted)
    {
        update_papertrail("Quote added", $sql);
        echo '<center><font color="#0000FF">Quote added, thanks!</font></center>';
    } // if
    return $inserted;
} // process_possible_submission


function render_submission_ui()
{
    $form = get_form_tag();

echo <<< EOF
    $form
    Whisper sweet nothings: <input type='text' size="60" name='quote'><br>
    (optional) email address: <input type='text' size="20" name='author'><br>
    <input type='submit' name='submitting' value='Go!'>
    </form>

EOF;
} // render_submission_ui


// The mainline...
render_header();
process_possible_submission();
render_submission_ui();
render_footer();

?>
