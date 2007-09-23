<?php
require_once 'common.php';
require_once 'database.php';
require_once 'headerandfooter.php';

render_header();

$query = do_dbquery('select * from quotes;');
if ($query == false)
    write_error('query failed');
else
{
    echo "<table>\n";
    while ( ($row = db_fetch_array($query)) != false )
    {
        echo "  <tr>\n";
        echo "    <td>id: ${row['id']}</td>\n";
        echo "    <td>text: ${row['text']}</td>\n";
        echo "    <td>public: ${row['public']}</td>\n";
        echo "    <td>author: ${row['author']}</td>\n";
        echo "    <td>entrydate: ${row['entrydate']}</td>\n";
        echo "    <td>lastedit: ${row['lastedit']}</td>\n";
        echo "  </tr>\n";
    }
    echo "</table>\n";
    echo "<hr>\n";
}

echo "<center>blah!</center>\n";  // !!! FIXME: do some magic here.
render_footer();
?>
