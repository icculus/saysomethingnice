<?php

require_once 'saysomethingnice.php';

function process_possible_submission()
{
    if (!has_input('submitting'))
        return;  // no submission, we'll just show the submission form later.

    if (!get_input_string('quote', 'Quote text', $quote)) return;
    if (!get_input_string('author', 'Email address', $author, '', true)) return;

    if (strlen($quote) > 512)
    {
        $escaped = escapehtml($quote);
        write_error("Sorry, your quote is too long.");
        echo "<center>\"$escaped\"</center><hr>\n";
        return;
    } // if

    if (strlen($author) > 128)  // just trim this, no need to fight.
        $author = substr($author, 0, 128);

    write_debug('Got an apparently non-bogus submission!');
    if (add_quote($quote, $author, ip2long($_SERVER['REMOTE_ADDR'])))
    {
        echo '<center><div class="sitestatus">Quote added, thanks!' .
             '<br/>Feel free to add another, if you like.</div></center><hr>';
    } // if
} // process_possible_submission


function render_submission_ui()
{
    $form = get_form_tag();
    $domain = get_domain_info();
    $instructions = $domain['addinstructions'];

echo <<< EOF
    $form
    <!-- google_ad_section_start -->
    <p>$instructions</p>
    <!-- google_ad_section_end -->
    <!-- google_ad_section_start(weight=ignore) -->
    <p>
    Your quote: <input type='text' size='60' maxlength='512' name='quote'><br>
    (optional) email address: <input type='text' size='20' maxlength='128' name='author'><br>
    <input type='submit' name='submitting' value='Go!'>
    </p>
    <!-- google_ad_section_end -->
    </form>

EOF;
} // render_submission_ui


// The mainline...
render_header();
process_possible_submission();
render_submission_ui();
render_footer();

?>
