<?php

require_once '../saysomethingnice.php';

if (!valid_admin_login())
{
    admin_login_prompt();
    exit(0);
} // if

// !!! FIXME: this is all EXPENSIVE...need to clean this up.

render_header(NULL, '', false);

$quotes = array();
$sql = "select id, text from quotes where approved=1";
$query = do_dbquery($sql);
if ($query != false)
{
    while (($row = db_fetch_array($query)) != false)
    {
        calculate_quote_rating($row['id'], $rating, $votes, $positive, $negative);
        $row['rating'] = $rating;
        $row['votes'] = $votes;
        $row['positive'] = $positive;
        $row['negative'] = $negative;
        $quotes[] = $row;
    } // while
} // if


function write_table($sorttype, $q)
{
    $adminurl = get_admin_url();
    echo "<hr><p><center><h1>$sorttype</h1></center></p>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<td>text</td>";
    echo "<td>rating</td>";
    echo "<td>votes</td>";
    echo "<td>ups</td>";
    echo "<td>downs</td>";
    echo "<td>pct</td>";
    echo "</tr>";
    foreach ($q as $i)
    {
        $id = $i['id'];
        $link = get_quote_url($id);
        $pct = ( ($i['votes'] == 0) ? 0 : ((int) (($i['positive'] / $i['votes']) * 100.0)) );
        echo "<tr>" .
               "<td>" .
                 "${i['text']}" .
                 " <font size=1>[ <a href='$link'>link</a> | <a href='$adminurl?action=edit&id=$id'>edit</a> ]</font>" .
               "</td>" .
               "<td>${i['rating']}</td>" .
               "<td>${i['votes']}</td>" .
               "<td>${i['positive']}</td>" .
               "<td>${i['negative']}</td>" .
               "<td>$pct</td>" .
             "</tr>";
    } // foreach
    echo "</table>";
} // write_table


function cmpweight($a, $b)
{
    $aval = $a['rating'] * $a['votes'];
    $bval = $b['rating'] * $b['votes'];
    if ($aval == $bval)
        return 0;
    return (($aval < $bval) ? 1 : -1);
} // cmpweight

if (usort($quotes, 'cmpweight'))
     write_table('sorted by weight (rating times votes)', $quotes);


function cmprating($a, $b)
{
    $aval = $a['rating'];
    $bval = $b['rating'];
    if ($aval == $bval)
        return 0;
    return (($aval < $bval) ? 1 : -1);
} // cmprating

if (usort($quotes, 'cmprating'))
    write_table('sorted by basic rating', $quotes);


function cmprating_votes4tie($a, $b)
{
    $aval = $a['rating'];
    $bval = $b['rating'];
    if ($aval == $bval)
    {
        $aval = $a['votes'];
        $bval = $b['votes'];
        if ($aval == $bval)
            return 0;
        return (($aval < $bval) ? 1 : -1);
    } // if
    return (($aval < $bval) ? 1 : -1);
} // cmprating

if (usort($quotes, 'cmprating_votes4tie'))
    write_table('sorted by basic rating, then total vote count', $quotes);



function cmppct_votes4tie($a, $b)
{
    $avotes = $a['votes'];
    $bvotes = $b['votes'];
    $aval = ( ($avotes == 0) ? 0 : ($a['positive'] / $avotes) );
    $bval = ( ($bvotes == 0) ? 0 : ($b['positive'] / $bvotes) );
    if ($aval == $bval)
    {
        if ($avotes == $bvotes)
            return 0;
        return (($avotes < $bvotes) ? 1 : -1);
    } // if
    return (($aval < $bval) ? 1 : -1);
} // cmppct_votes4tie

if (usort($quotes, 'cmppct_votes4tie'))
    write_table('sorted by positive percentage, then total vote count', $quotes);

echo "<hr>";

render_footer();

?>

