<?php

// localcfg.php must be supplied by you! it's not in source control, as it
//  depends on the server itself.
//
// It should have these globals defined:
//$dbhost = 'DATABASE_HOSTNAME_GOES_HERE';
//$dbuser = 'DATABASE_USERNAME_GOES_HERE';
//$dbpass = 'DATABASE_PASSWORD_GOES_HERE';
//$dbname = 'DATABASE_NAME_GOES_HERE';
//$geoipdb = 'PATH_TO_GEOIP_DAT_GOES_HERE';  // (or NULL).
//$geoiporgdb = 'PATH_TO_GEOIPORG_DAT_GOES_HERE';  // (or NULL).

require_once 'localcfg.php';


//$enable_debug = true;
//$enable_debug = false;
$enable_debug = (!empty($_REQUEST['debug']));
$always_show_papertrail = false;


function get_login(&$user, &$pass)
{
    if (isset($_SERVER['PHP_AUTH_USER']))
        $user = $_SERVER['PHP_AUTH_USER'];
    else if (isset($_REQUEST['user']))
        $user = $_REQUEST['user'];
    else
        $user = NULL;

    if (isset($_SERVER['PHP_AUTH_PW']))
        $pass = $_SERVER['PHP_AUTH_PW'];
    else if (isset($_REQUEST['pass']))
        $pass = $_REQUEST['pass'];
    else
        $pass = NULL;
} // get_login


function escapehtml($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
} // escapehtml


function get_form_tag()
{
    global $enable_debug;
    if ($enable_debug)
        return("<form><input type='hidden' name='debug' value='true' />");
    return("<form method='post' action='${_SERVER['PHP_SELF']}'>");
} // get_form_tag


function is_authorized()
{
    //return(!empty($_SERVER['REMOTE_USER']));
    return false;  // !!! FIXME: write me
} // is_authorized


function write_error($err)
{
    echo "\n<!-- google_ad_section_start(weight=ignore) -->\n";
    echo "<p><center><font color='#FF0000'>";
    echo   "ERROR: $err<br>";
    echo "</font></center>\n";
    echo "<!-- google_ad_section_end -->\n";
} // write_error


function write_debug($dbg)
{
    global $enable_debug;
    if ($enable_debug)
    {
        echo "\n<!-- google_ad_section_start(weight=ignore) -->\n";
        echo "<p><center><font color='#0000FF'>";
        echo   "DEBUG: $dbg<br>";
        echo "</font></center>\n";
        echo "<!-- google_ad_section_end -->\n";
    } // if
} // write_debug


function current_sql_datetime()
{
    $t = localtime(time(), true);
    return( "" . ($t['tm_year'] + 1900) . '-' .
                 ($t['tm_mon'] + 1) . '-' .
                 ($t['tm_mday']) . ' ' .
                 ($t['tm_hour']) . ':' .
                 ($t['tm_min']) . ':' .
                 ($t['tm_sec']) );
} // current_sql_datetime


function sql_datetime_to_unix_timestamp($sqldatetime)
{
    // !!! FIXME: this is a little hacky.
    return strtotime($sqldatetime . ' GMT');
} // sql_datetime_to_unix_timestamp


function has_input($reqname)
{
    $val = $_REQUEST[$reqname];
    if (isset($val))
        return (trim($val) != '');
    return false;
} // has_input


function get_input_sanitized($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    $val = $_REQUEST[$reqname];

    if (isset($val))
    {
        if (get_magic_quotes_gpc())  // so annoying.
            $val = stripslashes($val);
    } // if
    else
    {
        if (!isset($defval))
        {
            write_error("No $reqtype specified.");
            return false;
        } // if
        $reqval = $defval;
        return true;
    } // else

    $reqval = trim($val);
    if ((!$allowblank) && ($reqval == ''))
    {
        write_error("$reqtype is blank: Please fill out all fields.");
        return false;
    } // if

    return true;
} // get_input_sanitized


function get_input_string($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    return get_input_sanitized($reqname, $reqtype, $reqval, $defval, $allowblank);
} // get_input_string


function get_input_bool($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    $tmp = '';
    if (!get_input_sanitized($reqname, $reqtype, $tmp, $defval, $allowblank))
        return false;

    $tmp = strtolower($tmp);
    if (($tmp == 'y') || ($tmp == 'yes') ||
        ($tmp == 't') || ($tmp == 'true') ||
        ($tmp == '1') || ($tmp == 'up'))
    {
        $reqval = 1;
        return true;
    } // if

    if (($tmp == 'n') || ($tmp == 'no') ||
        ($tmp == 'f') || ($tmp == 'false') ||
        ($tmp == '0') || ($tmp == 'down'))
    {
        $reqval = 0;
        return true;
    } // if

    write_error("$reqtype is not true or false");
    return false;
} // get_input_bool


function get_input_number($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    if (!get_input_sanitized($reqname, $reqtype, $reqval, $defval, $allowblank))
        return false;

    list($hex) = sscanf($reqval, "0x%X");
    if (isset($hex)) // it's a 0xHEX value.
        $reqval = $hex;

    if (!is_numeric($reqval))
    {
        write_error("$reqtype isn't a number");
        return false;
    } // if

    return true;
} // get_input_number


function get_input_int($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    if (!get_input_number($reqname, $reqtype, $reqval, $defval, $allowblank))
        return false;

    $reqval = (int) $reqval;
    return true;
} // get_input_int


function get_input_float($reqname, $reqtype, &$reqval, $defval=NULL, $allowblank=false)
{
    if (!get_input_number($reqname, $reqtype, $reqval, $defval, $allowblank))
        return false;

    $reqval = (float) $reqval;
    return true;
} // get_input_float

?>
