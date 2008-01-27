<?php

require_once 'saysomethingnice.php';

$already_404 = false;
function do404($errstr = NULL)
{
    global $already_404, $enable_debug;
    if ($already_404)
        return;

    $already_404 = true;

    header('HTTP/1.0 404 Not Found');
    header('Connection: close');
    header('Content-Type: text/html');

    include '../htmlhead.php';
    echo "<h1>Photo retrieval</h1>\n";
    write_error("404 Not Found");
    if ( ($enable_debug) && (isset($errstr)) )
        write_error("'$errstr' ($errno) at $errfile:$errline\n");
    include '../htmlfoot.php';

    return 0;
}

// Suppress error messages, since we might not be writing html.
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    do404("'$errstr' ($errno) at $errfile:$errline\n");
    exit(0);
}

error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
set_error_handler('myErrorHandler');

if (!isset($_REQUEST['id']))
    return do404("id not specified");

$id = (int) $_REQUEST['id'];
if ($id == 0)
    return do404("id not valid");

$sql = "select data, mimetype from images where id=$id limit 1";
$query = db_doquery($sql);
if ($query == false)
    return do404("SQL query failed");

$row = db_fetch_array($query);
if ($row == false)
    return do404("no image in database");

header('Connection: close');
// !!! FIXME: header('ETag: ' . $metadata['ETag']);
// !!! FIXME: header('Last-Modified: ' . $metadata['Last-Modified']);
header('Content-Length: ' . strlen($row['data']));  // !!! FIXME: is this safe?
header('Accept-Ranges: bytes');
header('Content-Type: ' . $row['mimetype']);
print($row['data']);
db_free_result($query);

return 0;
?>