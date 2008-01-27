<?php

require_once 'saysomethingnice.php';

function process_vote()
{
    if (!get_input_string('thumbs', 'Thumb position', $thumbs)) return false;
    if (!get_input_int('id', 'quote id', $quoteid)) return false;

    if (($thumbs != 'up') and ($thumbs != 'down'))
    {
        write_error("'thumbs' isn't 'up' or 'down' ...");
        return false;
    } // if

    $rating = ($thumbs == 'up') ? 1 : -1;

    $sql = "select id,rating from votes where quoteid=$quoteid and ipaddr=$ipaddr limit 1";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;

    $row = db_fetch_array($query);
    if ($row == false)
    {
        // no previous vote, create one.
        add_rating($quoteid, $ipaddr, $rating);
    } // if
    else
    {
        $voteid = $row['id'];
        $oldrating = $row['rating'];
        if ($rating != $oldrating)
            update_rating($voteid, $ipaddr, $quoteid, $rating);
    } // else

    return true;
} // process_vote


// The mainline...
render_header();
if (process_vote())
    echo "<center>thanks!</center>";
render_footer();

?>
