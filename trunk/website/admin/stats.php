<?php

require_once '../saysomethingnice.php';

render_header(NULL, '', false);
$sql = "select domainname from domains order by domainname";
$query = do_dbquery($sql);
if ($query == false)
    return;  // do_dbquery will have spit out an error.

echo "<table align='center' width='30%'><tr><td>";
echo "<div class='box'>" .
        "<div class='boxtop'><div></div></div>" .
            "<div class='boxcontent'>";

$imgurl = get_static_imgdir_url();

echo "<table>\n";
echo "<tr><td align='center'><b>Statistics for various subdomains:</b></td></tr>\n";
echo "<tr><td><hr></td></tr>\n";
while (($row = db_fetch_array($query)) != false)
{
    $domain = $row['domainname'];
    echo "<tr><td align='center'>" .
           $domain . "&nbsp;" .
           "<a href='/statistics/awstats.${domain}.html'>" .
             "<img src='${imgurl}chainlinkicon_1.png'" .
             " style='border-style: none; vertical-align: middle'" .
             " alt='link' title='link to $domain stats' />" .
           "</a>" .
         "</td></tr>\n";
} // while
echo "</table>\n";
echo "</div><div class='boxbottom'><div></div></div></div></td></tr></table>\n";
echo "<br/>\n";

render_footer();

?>

