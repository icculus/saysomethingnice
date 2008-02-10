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

    $advertisements = $showads ? get_advertisements() : '';

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
         "<html xmlns='http://www.w3.org/1999/xhtml'>" .
           "<head>" .
             "<meta http-equiv='Content-Type' content='text/html;charset=utf-8' />" .
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

    echo     "<a href='${baseurl}'>Get another quote</a>" .
             "&nbsp;&nbsp;&nbsp;" .
             "<a href='${posturl}'>Add a quote</a>" .
             //"&nbsp;&nbsp;" .
             //"<a href='${widgeturl}'>Get Mac Widget</a>" .
           "</body>" .
         "</html>";
} // render_footer

?>
