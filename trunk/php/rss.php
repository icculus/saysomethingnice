<?php

require_once 'saysomethingnice.php';

$query = do_dbquery('select * from quotes where approved=true and deleted=false order by postdate desc limit 5');
if ($query == false)
{
    header('HTTP/1.0 500 Internal Server Error');
    render_header();
    write_error('query failed');
    render_footer();
    return;
} // if

header('Content-Type: application/rss+xml; charset=UTF-8');

$rowcount = db_num_rows($query);
$newestentrytime = current_sql_datetime();
if ($rowcount > 0)
{
    $row = db_fetch_array($query);
    if ($row != false)
        $newestentrytime = $row['postdate'];
    db_reset_array($query);
} // if

$pubdate = date(DATE_RSS, sql_datetime_to_unix_timestamp($newestentrytime));

$items = '';
$digestitems = '';
while ( ($row = db_fetch_array($query)) != false )
{
    $url = get_quote_url($row['id']);
    $text = escapehtml($row['text']);
    $desc = escapehtml(render_quote_to_string($row['text'], $row['id'], $row['imageid']));

    $postdate = date(DATE_RSS, sql_datetime_to_unix_timestamp($row['postdate']));
    $items .= "<item><title>\"${text}\"</title><pubDate>${postdate}</pubDate>" .
              "<description>${desc}</description>" .
              "<link>${url}</link></item>\n";
    $digestitems .= "<rdf:li rdf:resource=\"${url}\" />\n";
} // while
db_free_result($query);

$rssurl = get_rss_url();

// stupid question mark endtag screws up PHP, even in strings and comments!
$xmltag = '<' . '?' . 'xml version="1.0" encoding="UTF-8"' . '?' . '>';
echo <<<EOF
$xmltag
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns="http://purl.org/rss/1.0/">

  <channel rdf:about="$rssurl">
    <title>Quick, say something nice!</title>
    <link>${baseurl}</link>
    <description>Pulling your relationship out of the fire since 2008.</description>
    <pubDate>${pubdate}</pubDate>
    <items>
      <rdf:Seq>
        $digestitems
      </rdf:Seq>
    </items>
  </channel>

  $items

</rdf:RDF>

EOF;

?>
