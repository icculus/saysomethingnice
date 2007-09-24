<?php

require_once 'saysomethingnice.php';

function output_quote_queue_rows($category, $showall = 0)
{
    $sql = "select * from quotes where category=$category";
    if (!$showall)  // show only pending?
        $sql .= ' and (approved=false or deleted=true)';
    $sql .= ';';

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // do_dbquery will have spit out an error.

    $item_count = $approved = $pending = $deleted = 0;

    while (($row = db_fetch_array($query)) != false)
    {
        $item_count++;

        $row['author'] = htmlentities($row['author'], ENT_QUOTES);
        $row['text'] = htmlentities($row['text'], ENT_QUOTES);

        $tags = $endtags = '';
        if ($row['deleted'])
        {
            $tags    .= '<strike>';
            $endtags  = '</strike>' . $endtags;
            $deleted++;
        } // if

        if ($row['approved'])
        {
            $tags    .= '<i>';
            $endtags  = '</i>' . $endtags;
            $approved++;
        } // if
        else
        {
            $pending++;
        } // else

        $ip = long2ip($row['ipaddr']);

        print("<tr>\n");
        print('<td align="center"> <input type="checkbox" name="itemid[]"');
        print(" value=\"{$row['id']}\"></td>\n");

        print("<td align=\"center\"> $tags {$row['postdate']} $endtags </td>\n");

        print("<td align=\"center\"> $tags");
        print(" <a href=\"{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}\">");
        print("{$row['text']} $endtags </a> </td>\n");

        print("<td align=\"center\"> $tags {$row['author']} $endtags </td>\n");
        print("<td align=\"center\"> $tags $ip $endtags </td>\n");
        print("</tr>\n");
    } // while

    print('<tr><td align="center" colspan="5"><font color="#0000FF">');
    print("$item_count items listed, $deleted deleted, $approved approved, $pending pending.</font></td></tr>\n");
} // output_quote_queue_rows


function output_quote_queue_widgets()
{
    global $baseurl, $posturl;

    //!!! FIXME: abort if not logged in.

    // !!! FIXME: 'q' is a leftover from IcculusNews queues.
    get_input_int('q', 'Category ID number', $q, 0);
    get_input_bool('showall', 'show all posts', $showall, false);

    $showallflip = true;
    $showalltext = "Show all items";
    if ($showall)
    {
        $showallflip = false;
        $showalltext = "Show only pending items";
    } // if

    $form = get_form_tag();
    echo "$form <input type='hidden' name='showall' value='$showall'>\n";

    $query = do_dbquery("select id, name from categories order by id;");
    if ($query == false)
        return;  // error output is handled in database.php ...

    $category_count = db_num_rows($query);

    if ($category_count == 0)
        write_error('No categories? Your database is corrupt?');

    else if ($category_count == 1)  // no dropdown box.
    {
        $row = db_fetch_array($query);
        if ($row == false)
            write_error('Failed to enumerate categories.');
        else
        {
            if ($q == 0)
                $q = $row['id'];
            $catname = htmlentities($row['name'], ENT_QUOTES);
            $catlist = "Category: <i>$catname</i>";
        } // if
    } // else

    else if ($category_count > 1)
    {
        $catlist .= 'Category: <select name="catid" size="1">';
        $catlist .= "\n";

        while (($row = db_fetch_array($query)) != false)
        {
            $catid = $row['id'];
            $catname = $row['name'];
            $sel = (($catid == $q) ? 'selected' : '');
            $catname = htmlentities($catname, ENT_QUOTES);
            $catlist .= "<option $sel value=\"$catid\">$catname</option>\n";
        } // while

        $catlist .= "</select>\n";
        $catlist .= '<input type="submit" name="chcatid" value="Change">';
        $catlist .= "\n";
        $catlist .= '<input type="submit" name="mvcatid" value="Move Selected To">';
        $catlist .= "\n";
    } // else if

    db_free_result($query);

echo <<< EOF

    <center>
      <table border="0" width="100%">
        <tr>
          <td align="left">
            $catlist
          </td>
          <td align="right">
            [
            <a href="${_SERVER['PHP_SELF']}?showall=$showallflip">$showalltext</a>
            |
            <a href="${_SERVER['PHP_SELF']}?action=changepw">Change password</a>
            |
            <a href="${_SERVER['PHP_SELF']}?action=logout">Log out</a>
            ]
          </td>
        </tr>
      <table>

      <table border="1" width="100%">
        <tr>

          <script language="javascript">
          <!--
              function selectAll(formObj)
              {
                  var checkval = false;
                  var i;

                  for (i = 0; i < formObj.length; i++)
                  {
                      var fldObj = formObj.elements[i];
                      if ((fldObj.type == 'checkbox') &&
                          (fldObj.name == 'checkeverything'))
                      {
                          checkval = (fldObj.checked) ? true : false;
                          break;
                      }
                  }

                  if (i == formObj.length)  // ???
                      return;

                  for (i = 0; i < formObj.length; i++)
                  {
                      var fldObj = formObj.elements[i];
                      if (fldObj.type == 'checkbox')
                          fldObj.checked = checkval;
                  }
              }
          //-->
          </script>

          <td align="center">
            <script language="javascript">
            <!--
              document.write('<input type="checkbox" name="checkeverything"');
              document.write(' value="0" onClick="selectAll(this.form);">');
            //-->
            </script>
            <noscript>X</noscript>
          </td>

          <td align="center"> date </td>
          <td align="center"> text </td>
          <td align="center"> author </td>
          <td align="center"> ip addr </td>
        </tr>

EOF;

    if ($q != 0)
        output_quote_queue_rows($q, $showall);
    else
    {
        print('<tr><td colspan="5" align="center"><font color="#0000FF">');
        print("Please select a category from the above list.</font></td></tr>\n");
    } // else

echo <<< EOF

        <tr>
          <td align="center" colspan="5">
            <input type="submit" name="refresh"   value="Refresh">
            <input type="submit" name="delete"    value="Delete">
            <input type="submit" name="undelete"  value="Undelete">
            <input type="submit" name="approve"   value="Approve">
            <input type="submit" name="unapprove" value="Unapprove">
            <input type="submit" name="purge"     value="Purge Selected">
            <input type="submit" name="purgeall"  value="Purge All">
          </td>
        </tr>
      </table>
      </form>
    </center>
EOF;
} // output_quote_queue_widgets


function build_id_list($itemid, &$idlist)
{
    $idlist = '';
    if (isset($itemid))
    {
        $or = '';
        foreach ($itemid as $id)
        {
            if (!is_numeric($id))
            {
                write_error('bogus id specified');
                return;
            } // if
            $idnum = (int) $id;
            $idlist .= "${or}id=${idnum}";
            $or = 'or ';
        } // foreach

        if ($idlist != '')
            return true;
    } // if

    return false;
} // build_id_list


function process_delete_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set deleted=true, approved=false where deleted=false and $idlist;";
    $affected = do_dbupdate($sql);
    update_papertrail("deleted $affected quotes", $sql, $idlist);
} // process_delete_action


function process_undelete_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set deleted=false where deleted=true $idlist;"
    $affected = do_dbupdate($sql);
    update_papertrail("undeleted $affected quotes", $sql, $idlist);
} // process_undelete_action


function process_approve_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set approved=true where approved=false and deleted=false and $idlist;"
    $affected = do_dbupdate($sql);
    update_papertrail("approved $affected quotes", $sql, $idlist);
} // process_approve_action


function process_unapprove_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set approved=false where approved=true and $idlist;"
    $affected = do_dbupdate($sql);
    update_papertrail("unapproved $affected quotes", $sql, $idlist);
} // process_unapprove_action


function process_purge_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "delete from quotes where deleted=true and $idlist;"
    $affected = do_dbdelete($sql);
    update_papertrail("purged $affected quotes", $sql);
} // process_purge_action


function process_purgeall_action()
{
    $sql = "delete from quotes where deleted=true;"
    $affected = do_dbdelete($sql);
    update_papertrail("purged $affected quotes", $sql);
} // process_purgeall_action


function requested_action($name)
{
    if ((get_input_string($name, $name, $x, '', true)) && ($x != ''))
    {
        write_debug("requested action '$name'");
        return true;
    } // if

    return false;
} // requested_action


function process_possible_actions()
{
    if (requested_action('delete'))
        process_delete_action();
    else if (requested_action('undelete'))
        process_undelete_action();
    else if (requested_action('approve'))
        process_approve_action();
    else if (requested_action('unapprove'))
        process_unapprove_action();
    else if (requested_action('purge'))
        process_purge_action();
    else if (requested_action('purgeall'))
        process_purgeall_action();
} // process_possible_actions


// mainline...
render_header();
process_possible_actions();
output_quote_queue_widgets();
render_footer();

?>