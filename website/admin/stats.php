<?php

require_once '../saysomethingnice.php';

$imgurl = get_static_imgdir_url();

function echoOneDomain($domain)
{
    global $imgurl;
    echo "<tr><td align='center'>" .
           $domain . "&nbsp;" .
           "<a href='/statistics/awstats.${domain}.html'>" .
             "<img src='${imgurl}chainlinkicon_1.png'" .
             " style='border-style: none; vertical-align: middle'" .
             " alt='link' title='link to $domain stats' />" .
           "</a>" .
         "</td></tr>\n";
} // echoOneDomain


render_header(NULL, '', false);
$sql = "select id,domainname from domains order by domainname";
$query = do_dbquery($sql);
if ($query == false)
    return;  // do_dbquery will have spit out an error.

echo "<table align='center'><tr><td>";
echo "<div class='box'>" .
        "<div class='boxtop'><div></div></div>" .
            "<div class='boxcontent'>";

echo "<table>\n";
echo "<tr><td align='center'><b>Statistics for various subdomains:</b></td></tr>\n";
echo "<tr><td><hr></td></tr>\n";

$rows = array();
while (($row = db_fetch_array($query)) != false)
    $rows[] = $row;

// make sure main site is first.
foreach ($rows as $row)
{
    if ($row['id'] == 1)
    {
        echoOneDomain($row['domainname']);
        break;
    } // if
} // while

foreach ($rows as $row)
{
    if ($row['id'] != 1)
        echoOneDomain($row['domainname']);
} // while


echo "</table>\n";
echo "</div><div class='boxbottom'><div></div></div></div></td></tr></table>\n";
echo "<br/>\n";

render_footer();

?>

