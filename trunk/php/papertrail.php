<?php

require_once 'saysomethingnice.php';

function render_papertrail()
{
    // !!! FIXME: make a UI toggle for this.
    //if (!get_input_bool('showsql', 'should show sql', $showsql, 'y')) return;
    $showsql = true;

    $sql = 'select * from papertrail order by entrydate';
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    echo "<u>Current paper trail (oldest entries first)...</u>\n";
    echo "<ul>\n";
    
    while ( ($row = db_fetch_array($query)) != false )
    {
        $htmlaction = htmlentities($row['action'], ENT_QUOTES);
        $htmlauthor = htmlentities($row['author'], ENT_QUOTES);
        $htmlentrydate = htmlentities($row['entrydate'], ENT_QUOTES);
        $htmlsql = '';
        if ($showsql)
            $htmlsql = "<br>\n<code>" . htmlentities($row['sqltext'], ENT_QUOTES) . ";</code>";
        echo "  <li><b>$htmlaction</b>: <i>by $htmlauthor, on ${htmlentrydate}</i>${htmlsql}\n";
    } // while
    db_free_result($query);

    echo "</ul>\n";
    echo "<p>End of papertrail.\n";
} // op_renderpapertrail


// The mainline...
if (!valid_admin_login())
    admin_login_prompt();
else
{
    render_header();
    render_papertrail();
    render_footer();
} // else

?>
