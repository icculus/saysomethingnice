<?php

require_once '../saysomethingnice.php';
require_once 'geoip/geoipcity.inc';
require_once 'geoip/geoipregionvars.php';

$adminurl = get_admin_url();
$logouturl = $adminurl . "?action=logout";

function output_tooltip_script()
{
    // !!! FIXME: this should really be in tooltip.js instead.
echo <<< EOF

<div id="dhtmltooltip"></div>

<script type="text/javascript">

/***********************************************
* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function ddrivetip(thetext, thecolor, thewidth){
if (ns6||ie){
if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<tipobj.offsetWidth)
//move the horizontal position of the menu to the left by it's width
tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else
//position the horizontal position of the menu where the mouse is positioned
tipobj.style.left=curX+offsetxpoint+"px"

//same concept with the vertical position
if (bottomedge<tipobj.offsetHeight)
tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hideddrivetip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=''
tipobj.style.width=''
}
}

document.onmousemove=positiontip

</script>

EOF;
} // output_tooltip_script


function get_admin_names()
{
    $retval = array();
    $sql = 'select username from admins';
    $query = do_dbquery($sql);
    if ($query != false)
    {
        while (($row = db_fetch_array($query)) != false)
            $retval[] = $row['username'];
    } // if
    return $retval;
} // get_admin_names


function geostr($str)
{
    return ( (empty($str)) ? '???' : escapehtml($str) );
} // geostr


function output_quote_queue_rows($category, $showall = 0)
{
    global $adminurl, $geoipdb, $geoiporgdb, $GEOIP_REGION_NAME;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $gi = NULL;
    $giorg = NULL;

    if (isset($geoipdb))
    {
        // uncomment for Shared Memory support
        // geoip_load_shared_mem($geoipdb);
        // $gi = geoip_open($geoipdb, GEOIP_SHARED_MEMORY);
        $gi = geoip_open($geoipdb, GEOIP_STANDARD);
    } // if

    if (isset($geoiporgdb))
    {
        // uncomment for Shared Memory support
        // geoip_load_shared_mem($geoiporgdb);
        // $giorg = geoip_open($geoiporgdb, GEOIP_SHARED_MEMORY);
        $giorg = geoip_open($geoiporgdb, GEOIP_STANDARD);
    } // if

    $sql = "select * from quotes where domain=$domid and category=$category";
    if (!$showall)  // show only pending?
        $sql .= ' and (approved=false or deleted=true)';
    $sql .= ' order by id desc';

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // do_dbquery will have spit out an error.

    $item_count = $approved = $pending = $deleted = 0;

    while (($row = db_fetch_array($query)) != false)
    {
        $item_count++;

        calculate_quote_rating($row['id'], $rating, $votes, $positive, $negative);

        $row['author'] = escapehtml($row['author']);
        $row['text'] = escapehtml($row['text']);

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

        $geoipstr = '';
        if (isset($gi))
        {
            $record = geoip_record_by_addr($gi, $ip);
            if ($geoipstr != '')
                $geoipstr .= "<br/>";
            $geoipstr .= "country: " . geostr($record->country_name) . "<br/>" .
                         "region: " . geostr($GEOIP_REGION_NAME[$record->country_code][$record->region]) . "<br/>" .
                         "city: " . geostr($record->city) . "<br/>" .
                         "latitude: " . geostr($record->latitude) . "<br/>" .
                         "longitude: " . geostr($record->longitude) . "<br/>" .
                         "zip code: " . geostr($record->postal_code) . "<br/>" .
                         "dma code: " . geostr($record->dma_code) . "<br/>" .
                         "area code :" . geostr($record->area_code);
        } // if

        if (isset($giorg))
        {
            $record = geoip_org_by_addr($giorg, $ip);
            if ($geoipstr != '')
                $geoipstr .= "<br/>";
            $geoipstr .= "org: " . geostr($record);
        } // if

        if ($geoipstr != '')
            $ip = "<div onMouseover=\"ddrivetip('$geoipstr', 'yellow', 300);\" onMouseout=\"hideddrivetip();\">$ip</div>";

        print("<tr>\n");
        print('<td align="center"> <input type="checkbox" name="itemid[]"');
        print(" value=\"{$row['id']}\"></td>\n");

        print("<td align=\"center\"> $tags {$row['postdate']} $endtags </td>\n");

        print("<td align=\"center\"> $tags");
        print(" <a href=\"{$adminurl}?action=edit&id={$row['id']}\">");
        print("{$row['text']} $endtags </a> </td>\n");

        print("<td align=\"center\"> $tags {$row['author']} $endtags </td>\n");
        print("<td align=\"center\"> $tags $ip $endtags </td>\n");
        print("<td align=\"center\"> $tags $rating ($votes votes) $endtags </td>\n");
        print("</tr>\n");
    } // while

    if (isset($giorg))
        geoip_close($giorg);

    if (isset($gi))
        geoip_close($gi);

    print('<tr><td align="center" colspan="6"><font color="#0000FF">');
    print("$item_count items listed, $deleted deleted, $approved approved, $pending pending.</font></td></tr>\n");
} // output_quote_queue_rows


function output_quote_queue_widgets()
{
    global $baseurl, $adminurl, $logouturl;

    if (!valid_admin_login())
        return;  // just in case.

    // !!! FIXME: 'q' is a leftover from IcculusNews queues.
    get_input_int('q', 'Category ID number', $q, 0);
    get_input_bool('showall', 'show all posts', $showall, 'false');

    output_tooltip_script();

    $showallflip = 1;
    $showalltext = "Show all items";
    if ($showall)
    {
        $showallflip = 0;
        $showalltext = "Show only pending items";
    } // if

    get_login($user, $pass);
    $pass = NULL;
    echo "Logged in as: $user<br>\n";

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
            $catname = escapehtml($row['name']);
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
            $catname = escapehtml($catname);
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
            <a href="${adminurl}?showall=$showallflip">$showalltext</a>
            |
            <a href="${adminurl}?action=changepw">Change password</a>
            |
            <a href="$logouturl">Log out</a>
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
        print('<tr><td colspan="6" align="center"><font color="#0000FF">');
        print("Please select a category from the above list.</font></td></tr>\n");
    } // else

    $admins = get_admin_names();
    $adminoptlist = "<option selected='true' value=''>--choose--</option>\n";
    foreach ($admins as $adminname)
    {
        if ($adminname != $_SERVER['PHP_AUTH_USER'])
        {
            $adminname = escapehtml($adminname);
            $adminoptlist .= "<option value='$adminname'>$adminname</option>\n";
        } // if
    } // foreach

echo <<< EOF

        <tr>
          <td align="center" colspan="6">
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
            <input type="text" name="adminname" value="">
            <input type="submit" name="addadmin" value="Add Admin">
          </td>
          <td align="center" colspan="1">
            <select onchange="document.getElementById('deleteadmin').disabled = (this.selectedIndex == 0);" name="adminid" id="adminid" size="1">
            $adminoptlist
            </select>
            <script language="javascript">
            <!--
                function confirmadmindelete()
                {
                    var catname = document.getElementById('adminid');
                    var val = catname.options[catname.selectedIndex].value;
                    return window.confirm('Are you sure you want to delete the admin "' + val + '"?');
                } // confirmadmindelete
            //-->
            </script>
            <input type="submit" name="deleteadmin" id="deleteadmin" disabled="true" value="Delete Admin" onclick="return confirmadmindelete();">
          </td>
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


function output_edit_widgets($id)
{
    global $adminurl;

    $id = (int) $id;
    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sql = "select text,imageid,author,ipaddr,approved,deleted from quotes where id=$id and domain=$domid limit 1";
    $query = do_dbquery($sql);
    if ($query == false)
        return false;  // do_dbquery will have spit out an error.

    $row = db_fetch_array($query);
    if ($row == false)
    {
        write_error("No such quote...maybe it was deleted?");
        return false;
    } // if

    $text = escapehtml($row['text']);
    $imgid = $row['imageid'];
    $author = escapehtml($row['author']);
    $ipaddr = long2ip($row['ipaddr']);
    $approved = $row['approved'];
    $deleted = $row['deleted'];

    if (($approved) && ($deleted))
        $approved = false;

    $approvedchecked = ($approved) ? 'checked' : '';
    $deletedchecked = ($deleted) ? 'checked' : '';
    $unapprovedchecked = ((!$approved) && (!$deleted)) ? 'checked' : '';

    $imgtag = '<i>(no image uploaded.)</i>';
    if ( (isset($imgid)) && (((int) $imgid) > 0) )
    {
        $imgurl = get_img_url($imgid);
        $imgtag = "<img src='$imgurl' alt='image #$imgid' title='image #$imgid'/>";
    } // if

    $form = get_form_tag();
    echo "$form\n";
    echo "<input type='hidden' name='action' value='edit' />\n";
    echo "<input type='hidden' name='id' value='$id' />\n";
    echo "<table>\n";
    echo "<tr><td>Quote #$id</td></tr>\n";
    echo "<tr><td>Text:\n";
    echo "<input type='text' size='60' name='text' value='$text' /></td></tr>\n";
    echo "<tr><td>Email:\n";
    echo "<input type='text' size='60' name='author' value='$author' /></td></tr>\n";
    echo "<tr><td>IP address:\n";
    echo "<input type='text' size='60' name='ipaddr' value='$ipaddr' /></td></tr>\n";
    echo "<tr><td>\n";
    echo "<input type='radio' name='state' value='deleted' $deletedchecked />Deleted<br/>\n";
    echo "<input type='radio' name='state' value='unapproved' $unapprovedchecked />Unapproved<br/>\n";
    echo "<input type='radio' name='state' value='approved' $approvedchecked />Approved<br/>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "<input type='reset' name='editreset' value='Reset!' />\n";
    echo "<input type='submit' name='editsubmit' value='Change!' />\n";
    echo "</td></tr>\n";
    echo "</table></form>\n\n";

    echo "<form enctype='multipart/form-data' method='post' action='$adminurl'>";
    echo "<table>\n";
    echo "<input type='hidden' name='action' value='uploadpic' />\n";
    echo "<input type='hidden' name='id' value='$id' />\n";
    echo "<input type='hidden' name='MAX_FILE_SIZE' value='1024000' />\n";
    echo "<tr><td>Image: $imgtag</td></tr>\n";
    echo "<tr><td><input type='file' name='imgfile' />\n";
    echo "<input type='submit' name='uploadpicsubmit' value='Upload Image' /></td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";

    echo "<a href='$adminurl'>Nevermind.</a>\n";
    return true;  // don't show queue.
} // output_edit_widgets


// This code sucks.
function qmimetype($file)
{
    $ext = array_pop(explode('.', $file));
    foreach (file('./mime.types') as $line)
    {
        if (preg_match('/^([^#]\S+)\s+.*'.$ext.'.*$/', $line, $m))
            return $m[1];
    } // foreach

    return 'application/octet-stream';
} // qmimetype


function process_uploadpic_action()
{
    if (!get_input_int('id', 'Quote ID', $id))
        return false;

    else if (empty($_REQUEST['uploadpicsubmit']))
        return output_edit_widgets($id);

    else if (!isset($_FILES['imgfile']))
        return output_edit_widgets($id);
    
    else if ($_FILES['imgfile']['error'] != UPLOAD_ERR_OK)
        return output_edit_widgets($id);

    else if ($_FILES['imgfile']['size'] == 0)
        return output_edit_widgets($id);

    $filename = $_FILES['imgfile']['tmp_name'];
    $mime = 'image/jpeg';

    if (function_exists('finfo_open'))
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
    } // if

    else if (function_exists('mime_content_type'))
    {
        $mime = mime_content_type($filename);
    } // else if

    else if (false)
    {
        $escaped = escapeshellcmd($filename);
        $mime = `file --brief --mime $escaped`;
    } // else if

    else
    {
        $mime = qmimetype($_FILES['imgfile']['name']);
    } // else

    $bin = file_get_contents($filename);
    if ($bin === false)
        return output_edit_widgets($id);

    $ipaddr = ip2long($_SERVER['REMOTE_ADDR']);
    add_image($bin, $mime, $ipaddr, $id);

    return output_edit_widgets($id);
} // process_uploadpic_action


function process_edit_action()
{
    if (!get_input_int('id', 'Quote ID', $id))
        return false;

    else if (empty($_REQUEST['editsubmit']))
        return output_edit_widgets($id);

    else if (!get_input_string('text', 'Quote text', $text))
        return output_edit_widgets($id);
    
    else if (!get_input_string('author', 'Quote author', $author, '', true))
        return output_edit_widgets($id);
    
    else if (!get_input_string('ipaddr', 'IP address', $ipaddr))
        return output_edit_widgets($id);

    else if (!get_input_string('state', 'state', $state))
        return output_edit_widgets($id);

    else if (ip2long($ipaddr) == 0)
        return output_edit_widgets($id);

    $deleted = ($state == 'deleted');
    $approved = (($state == 'approved') && (!$deleted));

    if (!update_quote($id, $text, $author, ip2long($ipaddr), $approved, $deleted))
        return output_edit_widgets($id);

    return false;  // carry on.
} // process_edit_action


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
        return false;

    $sql = "update quotes set deleted=true, approved=false where deleted=false and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("deleted $affected quotes", $sql, $idlist, true);

    return false;  // carry on.
} // process_delete_action


function process_undelete_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return false;

    $sql = "update quotes set deleted=false where deleted=true and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("undeleted $affected quotes", $sql, $idlist, true);

    return false;  // carry on.
} // process_undelete_action


function process_approve_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return false;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sql = "update quotes set approved=true where domain=$domid and approved=false and deleted=false and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("approved $affected quotes", $sql, $idlist, true);

    return false;  // carry on.
} // process_approve_action


function process_unapprove_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return false;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sql = "update quotes set approved=false where domain=$domid and approved=true and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("unapproved $affected quotes", $sql, $idlist, true);

    return false;  // carry on.
} // process_unapprove_action


function process_purge_action()
{
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return false;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sql = "delete from quotes where domain=$domid and deleted=true and $idlist";
    $affected = do_dbdelete($sql, -1);
    update_papertrail("purged $affected quotes", $sql, NULL, true);

    return false;  // carry on.
} // process_purge_action


function process_purgeall_action()
{
    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sql = "delete from quotes where domain=$domid and deleted=true";
    $affected = do_dbdelete($sql, -1);
    update_papertrail("purged $affected quotes", $sql, NULL, true);

    return false;  // carry on.
} // process_purgeall_action


function process_addcategory_action()
{
    if (!get_input_string('catname', 'Category name', $catname))
        return false;
    add_category($catname);

    return false;  // carry on.
} // process_addcategory_action


function process_deletecategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return false;

    delete_category((int) $catid);
    $_REQUEST['q'] = 1;

    return false;  // carry on.
} // process_deletecategory_action


function process_changecategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return false;
    $_REQUEST['q'] = (int) $catid;

    return false;  // carry on.
} // process_changecategory_action


function process_movetocategory_action()
{
    if (!get_input_int('catid', 'Category ID', $catid))
        return false;
    if (!build_id_list($_REQUEST['itemid'], $idlist))
        return false;

    $domain = get_domain_info();
    $domid = (int) $domain['id'];

    $sqlid = db_escape_string($catid);
    $sql = "update quotes set category=$sqlid where domain=$domid and $idlist";
    $affected = do_dbupdate($sql, -1);
    update_papertrail("moved $affected quotes to category $catid", $sql, $idlist, true);

    return false;  // carry on.
} // process_movetocategory_action


function make_random_password()
{
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double) microtime() * 1000000);
    $i = 0;
    $pass = '';

    while ($i <= 7)
    {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    } // while

    return $pass;
} // make_random_password


function process_addadmin_action()
{
    global $logouturl;

    $password = make_random_password();

    if (!get_input_string('adminname', 'Admin name', $adminname))
        return false;

    if (!add_admin($adminname, $password))
        return false;

    $adminname = escapehtml($adminname);
    $password = escapehtml($password);

    get_login($user, $pass);
    $myname = urlencode($user);
    echo "<center><font color='#0000FF'>" .
         " {$adminname}'s initial password is: $password<br/>" .
         " Copy that to the clipboard now!</font><br/>" .
         " <font color='#FF0000'>" .
         " PLEASE <a href='$logouturl&oldlogin=$myname'>LOG IN AS '$adminname'</a>" .
         " AND CHANGE THE PASSWORD RIGHT THIS VERY MINUTE.</font></center>";

    return true;  // stop normal widgets from rendering.
} // process_addadmin_action


function process_deleteadmin_action()
{
    if (!get_input_string('adminid', 'Admin name', $adminname))
        return false;

    get_login($user, $pass);
    if ($adminname == $user)
    {
        write_error("You can't delete yourself.");
        return false;
    } // if

    if ( ($adminname == '') || (!delete_admin($adminname)) )
        return false;

    return false;  // output widgets.
} // process_deleteadmin_action


function output_changepw_widgets()
{
    global $adminurl;

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
    echo "<tr><td>\n";
    echo "<input type='reset' name='changepwreset' value='Reset!' />";
    echo "<input type='submit' name='changepwsubmit' value='Change!' />\n";
    echo "</td></tr>\n";
    echo "<tr><td><a href='$adminurl'>Nevermind.</a></td></tr>\n";
    echo "</table></form>\n\n";
} // output_changepw_widgets


function process_changepw_action()
{
    global $adminurl;

    if (!valid_admin_login())  // shouldn't happen, but just in case.
    {
        write_error("You don't seem to be logged in. Can't change password.");
        return true;  // don't go on.
    } // if

    get_login($user, $pass);
    $htmluser = escapehtml($user);
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

    if ($_REQUEST['oldpass'] != $pass)
    {
        if (!empty($pass))
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

    echo "<center>\n";
    echo "Okay, password changed!<br>\n";
    echo "You'll need to <a href=\"$adminurl\">log in again</a>.<br>\n";
    echo "</center>\n";

    return true;  // don't go on.
} // process_changepw_action


function process_logout_action()
{
    global $adminurl;

    if (valid_admin_login())  // switching users maybe?
    {
        if (isset($_REQUEST['oldlogin']))
        {
            get_login($user, $pass);
            if ($_REQUEST['oldlogin'] != $user)
            {
                // push browser to the non ?action=logout version, where they'll be
                //  prompted for a password again, but they won't be in a loop here.
                header("HTTP/1.0 307 Temporary redirect");
                header("Location: $adminurl");
                exit(0);
            } // if
        } // if
    } // if

    // apparently sending an HTTP 401 will cause most browsers to
    //  flush their auth cache for the realm.
    admin_login_prompt();
    exit(0);  // don't go on, ever.
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
    else if (requested_action('uploadpic'))
        return process_uploadpic_action();
    else if (requested_action('edit'))
        return process_edit_action();
    else if (requested_action('addadmin'))
        return process_addadmin_action();
    else if (requested_action('deleteadmin'))
        return process_deleteadmin_action();

    return false;
} // process_possible_actions


// mainline...

if (requested_action('logout'))
{
    process_logout_action();  // have to do this before any output.
    exit(0);
} // if

$always_show_papertrail = true;
if (!valid_admin_login())
    admin_login_prompt();
else
{
    $firehoseurl = get_firehose_url();
    render_header(NULL, "<link rel='alternate' type='application/rss+xml' title='Firehose' href='${firehoseurl}' />", false);
    if (!process_possible_actions())
        output_quote_queue_widgets();
    render_footer();
} // else

?>
