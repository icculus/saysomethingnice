<?php

require_once 'saysomethingnice.php';

function process_possible_submission()
{
    if (!has_input('submitting'))
        return;  // no submission, we'll just show the submission form later.

    if (!get_input_string('quote', 'Quote text', $quote)) return;
    if (!get_input_string('author', 'Email address', $author, '', true)) return;

    write_debug('Got an apparently non-bogus submission!');
    if (add_quote($quote, $author, ip2long($_SERVER['REMOTE_ADDR'])))
    {
        echo '<center><font color="#0000FF">Quote added, thanks!' .
             '<br/>Feel free to add another, if you like.</font></center><hr>';
    } // if
} // process_possible_submission


function render_submission_ui()
{
    $form = get_form_tag();

echo <<< EOF
    $form
    <!-- google_ad_section_start -->
    <p>Go ahead and add a compliment, apology, encouragement, or some other sweetness.<br/>
    It will show up on the front page once the crew comes along
    and makes sure it isn't spam or anything.</p>
    <!-- google_ad_section_end -->
    <!-- google_ad_section_start(weight=ignore) -->
    <p>
    Your quote: <input type='text' size="60" name='quote'><br>
    (optional) email address: <input type='text' size="20" name='author'><br>
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
