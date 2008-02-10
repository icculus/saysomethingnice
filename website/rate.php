<?php

require_once 'saysomethingnice.php';

function process_vote($quoteid)
{
    if (!get_input_bool('thumbs', 'Thumb position', $thumbs)) return false;

    $rating = ($thumbs) ? 1 : -1;
    $ipaddr = ip2long($_SERVER['REMOTE_ADDR']);

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
if (get_input_int('id', 'Quote ID number', $id))
{
    if (process_vote($id))
        echo "<center><font color='#0000FF'>thanks for voting!</font></center><hr>\n";
    render_specific_quote($id);
} // if
render_footer();

?>
