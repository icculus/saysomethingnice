<?php

require_once('twitteroauth/twitteroauth.php');
require_once('twitter.php');
require_once('saysomethingnice.php');

function isTwitterError($response, $what)
{
    global $hitRateLimit;

    if (!isset($response->errors))
        return false;

    print("$what failed:\n");

    foreach ($response->errors as $err)
    {
        print("  - {$err->message}\n");
        if ($err->code == 88)
            $hitRateLimit = true;
    }

    return true;
}

function tweet($domain, $twitter, $username, $userid)
{
    $delay = 27;
    $sql = "select t.id from tweets as t" .
           " inner join quotes as q on (t.quoteid=q.id)" .
           " where (t.userid=$userid) and (q.domain=${domain['id']}) and" .
           " (t.postdate between DATE_SUB(NOW(), INTERVAL $delay HOUR) and NOW())" .
           " limit 1";
    $query = do_dbquery($sql);

    if ($query == false)
    {
        echo "Query for latest tweet to user $userid failed.\n";
        return;
    } // if

    $count = db_num_rows($query);
    db_free_result($query);
    if ($count > 0)
    {
//        echo "Still too soon to tweet to user $userid.\n";
        return;
    } // if

    $sql = "select q.id, q.text from quotes as q where" .
           " (q.id <> ALL (select quoteid from tweets where userid=$userid)) and" .
           " (q.domain=${domain['id']}) and (q.approved=1) and (q.deleted=0)" .
           " and (LENGTH(q.text)<=140) order by RAND() limit 1";

    $query = do_dbquery($sql);
    if ($query == false)
    {
        echo "Query for random tweet to user $userid failed.\n";
        return;
    } // if

    $count = db_num_rows($query);
    if ($count <= 0)
    {
        db_free_result($query);
//        echo "No quotes available for random tweet to user $userid.\n";
        return;
    } // if

    $row = db_fetch_array($query);
    if ($row == false)
    {
        echo "Fetch query for random tweet to user $userid failed.\n";
        return;
    } // if

    $quoteid = $row['id'];
    $txt = $row['text'];
    db_free_result($query);

    $tweet = NULL;
    if ($userid == 0)
        $tweet = $twitter->post('statuses/update', array('status' => $txt));
    else
    {
        echo "!!! FIXME: write me (direct message support)\n";
        return;
    }

    if (isTwitterError($tweet, "tweeting '$txt'"))
    {
        echo "Failed to post tweet.\n";
        return;
    } // if

    $sql = "insert into tweets (quoteid, userid, postdate) values" .
           " ($quoteid, $userid, NOW())";
    $inserted = (do_dbinsert($sql) == 1);
//    echo "Tweeted '$txt' to user $userid.\n";
} // tweet


function do_tweeting($domain)
{
    if (!isset($domain['twitteruser']))
        return;
    else if (!isset($domain['twitterpass']))
        return;

//    echo "Tweeting for domain ${domain['domainname']} ...\n";
//    $twitter = new Twitter($domain['twitteruser'], $domain['twitterpass']);
//    $twitter->setUserAgent('tweet-nothings/1.0');
    $twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
    $twitter->get('account/verify_credentials');

    tweet($domain, $twitter, '', 0);

// !!! FIXME: maybe later
/*
    $followers = $twitter->getFollowers();
    foreach ($followers as $user)
        tweet($domain, $twitter, $user['screen_name'], $user['id']);
*/

} // do_tweeting



// mainline ...

$sql = "select * from domains where disabled=0";
$query = do_dbquery($sql, NULL, true);
if ($query == false)
{
    echo("couldn't get domain list\n");
    exit(1);
} // if

while ( ($domain = db_fetch_array($query)) != false )
    do_tweeting($domain);

exit(0);

?>
