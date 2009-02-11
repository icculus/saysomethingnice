<?php

require_once 'common.php';

function render_header($title=NULL, $headextras='', $showads=true)
{
    $domain = get_domain_info();

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-cache');

    $rssurl = get_rss_url();
    $posturl = get_post_url();
    $basecssurl = get_basecss_url();
    $cssurl = get_css_url();
    $imgurl = get_static_imgdir_url();

    $advertisements = $showads ? get_advertisements() : '';
    $tagline = escapehtml($domain['tagline']);
    $realname = escapehtml($domain['realname']);
    $logotext = escapehtml($domain['logotext']);

    if ($title == NULL)
        $title = $realname;

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
         "<html xmlns='http://www.w3.org/1999/xhtml'>" .
           "<head>" .
             "<meta http-equiv='Content-Type' content='text/html;charset=utf-8' />" .
             "<meta http-equiv='Cache-Control' content='no-cache' />" .
             $headextras .
             "<title>$title</title>" .
             "<link rel='icon' type='image/png' href='/favicon.png' />" .
             "<link rel='alternate' type='application/rss+xml'" .
             " title='Speed Feed' href='${rssurl}' />" .
             "<link rel='stylesheet' type='text/css' href='${basecssurl}'" .
             " media='screen' charset='utf-8' />" .
             "<link rel='stylesheet' type='text/css' href='${cssurl}'" .
             " media='screen' charset='utf-8' />" .
           "</head>" .
           "<body style='text-align: center'>" .
             $advertisements .
             "<p>" .
               "<!-- google_ad_section_start -->\n" .
               "<div class='tagline'>$tagline</div>" .
                 //"<img src='${imgurl}header.jpg'" .
                 //" alt='$realname'" .
                 //" title='$logotext' />\n" .
                 "<div class='logorealname'>$realname</div>\n" .
                 "<div class='logotext'>$logotext</div>\n" .
               "<!-- google_ad_section_end -->\n" .
             "</p>";

    write_debug('If you can read this, debugging is enabled!');
} // render_header


function render_footer()
{
    $baseurl = get_base_url();
    $posturl = get_post_url();
    //$widgeturl = get_widget_url();
    $mailtourl = get_contact_url();
    $twitterurl = get_twitter_url();

    $domain = get_domain_info();
    $thisdom = (int) $domain['id'];

    $otherdoms = '';
    // !!! FIXME: I'd rather not do a DB lookup here...
    $sql = "select domainname, shortname from domains where (id<>$thisdom) and (disabled=0) and (public=1) order by id";
    $query = do_dbquery($sql);
    if ($query != false)
    {
        while (($row = db_fetch_array($query)) != false)
            $otherdoms .= "&nbsp;&nbsp;&nbsp;<a href='http://{$row['domainname']}/'>${row['shortname']} qssn</a>";
    } // if    

    $twitterlink = '';
    if ($twitterurl != '')
        $twitterlink = "&nbsp;&nbsp;&nbsp;<a href='${twitterurl}'>Twitter</a>";

    echo     "\n<!-- google_ad_section_start(weight=ignore) -->\n" .
             "<a href='${baseurl}'>Get another quote</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<a href='${posturl}'>Add a quote</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<a href='${mailtourl}'>Contact us</a>" .
             $twitterlink .
             //"&nbsp;&nbsp;" .
             //"<a href='${widgeturl}'>Get Mac Widget</a>" .
             //"&nbsp;&nbsp;&nbsp;" .
             //"<script type='text/javascript'><!--\n" .
             //  "digg_bgcolor = document.body.bgColor;" .
             //  "digg_skin = 'compact';" .
             //  "digg_url = '${baseurl}';" .
             //  "\n// -->\n" .
             //"</script>" .
             //"<script src='http://digg.com/tools/diggthis.js' type='text/javascript'></script>\n" .
             $otherdoms .
             "<!-- google_ad_section_end -->\n" .
           "</body>" .
         "</html>";
} // render_footer

?>
