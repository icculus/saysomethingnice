<?php

require_once 'saysomethingnice.php';

function output_quote_queue_rows($category, $showall = 0)
{
    $sql = "select * from quotes where category=$category";
    if (!$showall)  // show only pending?
        $sql .= ' and (approved=false or deleted=true)';

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // do_dbquery will have spit out an error.

    $item_count = $approved = $pending = $deleted = 0;

    while (($row = db_fetch_array($query)) != false)
    {
        $item_count++;

        calculate_quote_rating($row['id'], $rating, $votes);

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
        print("<td align=\"center\"> $tags $rating ($votes votes) $endtags </td>\n");
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
    get_input_bool('showall', 'show all posts', $showall, 'false');

    $showallflip = 1;
    $showalltext = "Show all items";
    if ($showall)
    {
        $showallflip = 0;
        $showalltext = "Show only pending items";
    } // if

    $form = get_form_tag();
    echo "$form <input type='hidden' name='showall' value='$showall'>\n";

    $query = do_dbquery("select id, name from categories order by id");
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
        echo <<< EOF

    <script language="javascript">
    <!--
        function confirmdelete()
        {
            var catname = document.getElementById('catid');
            var text = catname.options[catname.selectedIndex].text;
            var val = catname.options[catname.selectedIndex].value;
            if (val == 1)
            {
                window.alert("You can't delete the '" + text + "' category. It's the default one.");
                return false;
            } // if
            return window.confirm('Are you sure you want to delete "' + text + '"?');
        } // confirmdelete

        function allowdelete(val)
        {
            var widget = document.getElementById('deletecategory');
            widget.disabled = (val == 1);  // don't delete default queue.
        } // allowdelete
    // -->
    </script>

EOF;

        $catlist .= 'Category: <select onchange="allowdelete(this.options[this.selectedIndex].value);" name="catid" id="catid" size="1">';
        $catlist .= "\n";

        while (($row = db_fetch_array($query)) != false)
        {
            $catid = $row['id'];
            $catname = $row['name'];
            $sel = (($catid == $q) ? 'selected' : '');
            $catname = htmlentities($catname, ENT_QUOTES);
            $catlist .= "<option $sel value=\"$catid\">$catname</option>\n";
        } // while

        $disabled = ($q == 1) ? ' disabled="true" ' : '';

        $catlist .= "</select>\n";
        $catlist .= '<input type="submit" name="chcatid" value="Change To">';
        $catlist .= "\n";
        $catlist .= '<input type="submit" name="mvcatid" value="Move Selected To">';
        $catlist .= "\n";
        $catlist .= '<input type="submit"' . $disabled . 'id="deletecategory" name="deletecategory" value="Delete Category" onclick="return confirmdelete();" >';
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
          <td align="center"> rating </td>
        </tr>

EOF;

    if ($q == 0)
        $q = 1;  // just put it in unsorted category for now.

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
      <input type='hidden' name='q' value='$q'>
      </form>

      $form
      <input type='hidden' name='showall' value='$showall'>
      <input type='hidden' name='q' value='$q'>
      <table border="0" width="100%">
        <tr>
          <td align="center" colspan="1">
            <input type="text" name="catname" value="">
            <input type="submit" name="addcategory" value="Add Category">
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
            $or = ' or ';
        } // foreach

        if ($idlist != '')
        {
            $idlist = '(' . $idlist . ')';
            return true;
        } // if
    } // if

    return false;
} // build_id_list


function process_delete_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set deleted=true, approved=false where deleted=false and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("deleted $affected quotes", $sql, $idlist);

    return false;  // carry on.
} // process_delete_action


function process_undelete_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set deleted=false where deleted=true and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("undeleted $affected quotes", $sql, $idlist);

    return false;  // carry on.
} // process_undelete_action


function process_approve_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set approved=true where approved=false and deleted=false and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("approved $affected quotes", $sql, $idlist);

    return false;  // carry on.
} // process_approve_action


function process_unapprove_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "update quotes set approved=false where approved=true and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("unapproved $affected quotes", $sql, $idlist);

    return false;  // carry on.
} // process_unapprove_action


function process_purge_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sql = "delete from quotes where deleted=true and $idlist";
    $affected = do_dbdelete($sql, -1);
    update_papertrail("purged $affected quotes", $sql);

    return false;  // carry on.
} // process_purge_action


function process_purgeall_action()
{
    $sql = "delete from quotes where deleted=true";
    $affected = do_dbdelete($sql, -1);
    update_papertrail("purged $affected quotes", $sql);

    return false;  // carry on.
} // process_purgeall_action


function process_addcategory_action()
{
    if (!get_input_string('catname', 'Category name', $catname))
        return;
    add_category($catname);

    return false;  // carry on.
} // process_addcategory_action


function process_deletecategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return;

    delete_category((int) $catid);
    $_REQUEST['q'] = 1;

    return false;  // carry on.
} // process_deletecategory_action


function process_changecategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return;
    $_REQUEST['q'] = (int) $catid;

    return false;  // carry on.
} // process_changecategory_action


function process_movetocategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return;
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return;

    $sqlid = db_escape_string($catid);
    $sql = "update quotes set category=$sqlid where $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("moved $affected quotes to category $catid", $sql, $idlist);

    return false;  // carry on.
} // process_movetocategory_action


function output_changepw_widgets()
{
    $me_url = $_SERVER['PHP_SELF'];
    $form = get_form_tag();
    echo "$form\n";
    echo "<input type='hidden' name='action' value='changepw' />\n";
    echo "<table>\n";
    echo "<tr><td>Current password:\n";
    echo "<input type='password' name='oldpass' value='' /></td></tr>\n";
    echo "<tr><td>New password:\n";
    echo "<input type='password' name='newpass1' value='' /></td></tr>\n";
    echo "<tr><td>Retype new password:\n";
    echo "<input type='password' name='newpass2' value='' /></td></tr>\n";
    echo "<tr>\n";
    echo "<td><input type='submit' name='changepwsubmit' value='Change!' /></td>\n";
    echo "<td><input type='reset' name='changepwreset' value='Reset!' /></td>\n";
    echo "</tr>\n";
    echo "<tr><td><a href='$me_url'>Nevermind.</a></td></tr>\n";
    echo "</table></form>\n\n";
} // output_changepw_widgets


function process_changepw_action()
{
    if (!valid_admin_login())  // shouldn't happen, but just in case.
    {
        write_error("You don't seem to be logged in. Can't change password.");
        return true;  // don't go on.
    } // if

    $user = $_SERVER['PHP_AUTH_USER'];
    $htmluser = htmlentities($user, ENT_QUOTES);
    echo "Changing password for $htmluser...<br>\n";

    if ( (empty($_REQUEST['oldpass'])) || 
         (empty($_REQUEST['newpass1'])) ||
         (empty($_REQUEST['newpass2'])) )
    {
        if (!empty($_REQUEST['changepwsubmit']))
            write_error("Please complete all fields.");
        output_changepw_widgets();
        return true;  // don't go on.
    } // if

    if ($_REQUEST['oldpass'] != $_SERVER['PHP_AUTH_PW'])
    {
        sleep(3);  // prevent brute force.
        write_error("Old password is incorrect.");
        output_changepw_widgets();
        return true;  // don't go on.
    } // if

    if ($_REQUEST['newpass1'] != $_REQUEST['newpass2'])
    {
        write_error("Passwords don't match.");
        output_changepw_widgets();
        return true;  // don't go on.
    } // if

    if ($_REQUEST['newpass1'] == '')
    {
        write_error("Can't have a blank password!");
        output_changepw_widgets();
        return true;  // don't go on.
    } // if

    if (!change_admin_password($user, $_REQUEST['oldpass'], $_REQUEST['newpass1']))
    {
        output_changepw_widgets();
        return true;  // don't go on.
    } // if

    $me_url = $_SERVER['PHP_SELF'];
    echo "<center>\n";
    echo "Okay, password changed!<br>\n";
    echo "You'll need to <a href=\"$me_url\">log in again</a>.<br>\n";
    echo "</center>\n";

    return true;  // don't go on.
} // process_changepw_action


function process_logout_action()
{
    // apparently sending an HTTP 401 will cause most browsers to
    //  flush their auth cache for the realm.
    admin_login_prompt();
    return true;  // don't go on.
} // process_logout_action


function requested_action($name)
{
    if ((get_input_string('action', 'action', $x, '', true)) && ($x == $name))
    {
        write_debug("requested action '$name'");
        return true;
    } // if

    else if ((get_input_string($name, $name, $x, '', true)) && ($x != ''))
    {
        write_debug("requested action '$name'");
        return true;
    } // if

    return false;
} // requested_action


function process_possible_actions()
{
    if (requested_action('delete'))
        return process_delete_action();
    else if (requested_action('undelete'))
        return process_undelete_action();
    else if (requested_action('approve'))
        return process_approve_action();
    else if (requested_action('unapprove'))
        return process_unapprove_action();
    else if (requested_action('purge'))
        return process_purge_action();
    else if (requested_action('purgeall'))
        return process_purgeall_action();
    else if (requested_action('addcategory'))
        return process_addcategory_action();
    else if (requested_action('deletecategory'))
        return process_deletecategory_action();
    else if (requested_action('chcatid'))
        return process_changecategory_action();
    else if (requested_action('mvcatid'))
        return process_movetocategory_action();
    else if (requested_action('changepw'))
        return process_changepw_action();

    return false;
} // process_possible_actions


// mainline...
if (!valid_admin_login())
    admin_login_prompt();
else if (requested_action('logout'))
    process_logout_action();  // have to do this before any output.
else
{
    render_header();
    if (!process_possible_actions())
        output_quote_queue_widgets();
    render_footer();
} // else

?>