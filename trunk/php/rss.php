<?php

require_once 'saysomethingnice.php';

$query = do_dbquery('select * from quotes where public=true order by entrydate desc limit 5;');
if ($query == false)
{
    header('HTTP/1.0 500 Internal Server Error');
    render_header();
    write_error('query failed');
    render_footer();
    return;
} // if

$rowcount = db_num_rows($query);
$newestentrytime = time();
if ($rowcount > 0)
{
    $row = db_fetch_array($query);
    if ($row != false)
        $newestentrytime = $row['entrydate'];
    db_reset_array($query);
} // if

$pubdate = date(DATE_RSS, $newestentrytime);

$items = '';
while ( ($row = db_fetch_array($query)) != false )
{
    $text = htmlentities($row['text'], ENT_QUOTES);
    $entrydate = htmlentities($row['entrydate'], ENT_QUOTES);
    $items .= "<item><title>\"$text\"</title><pubDate>${entrydate}</pubDate><description>\"$text\"</description></item>\n";
} // while
db_free_result($query);

// stupid '?>' screws up PHP.
$xmltag = '<' . '?' . 'xml version="1.0" encoding="UTF-8"' . '?' . '>';
echo <<<EOF
$xmltag
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns="http://purl.org/rss/1.0/">

  <channel rdf:about="$rssurl">
    <title>Quick, say something nice!</title>
    <link>${baseurl}</link>
    <description>Pulling your relationship out of the fire since 2007.</description>
    <pubDate>${puddate}</pubDate>
  </channel>

  $items

</rdf:RDF>

EOF;

?>