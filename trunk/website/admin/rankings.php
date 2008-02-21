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
$domain = get_domain_info();
$domid = (int) $domain['id'];
$sql = "select id, text from quotes where domain=$domid and approved=1 and deleted=0";
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

    $total_votes = 0;
    $total_rating = 0;
    $total_positive = 0;
    $total_negative = 0;

    foreach ($q as $i)
    {
        $id = $i['id'];
        $votes = $i['votes'];
        $rating = $i['rating'];
        $positive = $i['positive'];
        $negative = $i['negative'];
        $text = $i['text'];

        $total_votes += $votes;
        $total_rating += $rating;
        $total_positive += $positive;
        $total_negative += $negative;

        $link = get_quote_url($id);
        $pct = ( ($votes == 0) ? 0 : ((int) (($positive / $votes) * 100.0)) );

        echo "<tr>" .
               "<td>" .
                 "$text" .
                 " <font size=1>[ <a href='$link'>link</a> | <a href='$adminurl?action=edit&id=$id'>edit</a> ]</font>" .
               "</td>" .
               "<td>$rating</td>" .
               "<td>$votes</td>" .
               "<td>$positive</td>" .
               "<td>$negative</td>" .
               "<td>$pct</td>" .
             "</tr>";
    } // foreach

    $total_pct = ( ($total_votes == 0) ? 0 : ((int) (($total_positive / $total_votes) * 100.0)) );

    echo "<tr>" .
           "<td>" .
             "<i>(TOTALS VALUES FOR ALL QUOTES)</i>" .
           "</td>" .
           "<td>$total_rating</td>" .
           "<td>$total_votes</td>" .
           "<td>$total_positive</td>" .
           "<td>$total_negative</td>" .
           "<td>$total_pct</td>" .
         "</tr>" .
         "</table>";
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


render_footer();

?>

