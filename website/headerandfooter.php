<?php

require_once 'common.php';

function render_header($title=NULL, $headextras='', $showads=true)
{
    $rssurl = get_rss_url();
    $posturl = get_post_url();
    $cssurl = get_css_url();
    $imgurl = get_static_imgdir_url();

    if ($title == NULL)
        $title = 'Quick, say something nice!';

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-cache');

    $advertisements = $showads ? get_advertisements() : '';

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
         "<html xmlns='http://www.w3.org/1999/xhtml'>" .
           "<head>" .
             "<meta http-equiv='Content-Type' content='text/html;charset=utf-8' />" .
             "<meta http-equiv='Cache-Control' content='no-cache' />" .
             $headextras .
             "<title>$title</title>" .
             "<link rel='alternate' type='application/rss+xml'" .
             " title='Speed Feed' href='${rssurl}' />" .
             "<link rel='stylesheet' type='text/css' href='${cssurl}'" .
             " media='screen' charset='utf-8' />" .
           "</head>" .
           "<body style='text-align: center'>" .
             $advertisements .
             "<p>" .
               "<font size='-1'>Flowers? Candy? Jewelry? Relationship advice? No time for that!</font><br/>" .
               "<img src='${imgurl}header.jpg'" .
               " alt='Quick, Say Something Nice!' />" .
             "</p>";

    write_debug('If you can read this, debugging is enabled!');
} // render_header


function render_footer()
{
    $baseurl = get_base_url();
    $posturl = get_post_url();
    $widgeturl = get_widget_url();
    $mailtourl = get_contact_url();

    echo     "<a href='${baseurl}'>Get another quote</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<a href='${posturl}'>Add a quote</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<a href='${mailtourl}'>Contact us</a>" .
             //"&nbsp;&nbsp;" .
             //"<a href='${widgeturl}'>Get Mac Widget</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<script type='text/javascript'>" .
               "digg_bgcolor = '#90CE90';" .
               "digg_skin = 'compact';" .
               "digg_url = '${baseurl}';" .
             "</script>" .
             "<script src='http://digg.com/tools/diggthis.js' type='text/javascript'></script>" .
           "</body>" .
         "</html>";
} // render_footer

?>
