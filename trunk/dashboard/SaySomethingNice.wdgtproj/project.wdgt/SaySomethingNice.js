var widgeturl = 'http://centralserver/saysomethingnice/widget.php';
var last_updated = 0;
var xml_request = null;

function load()  // called in <body> onload
{
    if (!window.widget)
        show();
} // load

function updateContent(str)
{
    var content = document.getElementById("content");
    content.style.display = "block";
    content.innerHTML = str;
} // updateContent

function loaded(e, request)  // called when new page is pulled from server.
{
    xml_request = null;
    if (request.responseText)
    {
        last_updated = (new Date).getTime();
        updateContent(request.responseText);
    } // if
} // loaded

function show()
{
    var now = (new Date).getTime();

    // only check if 15 minutes have passed
    if ((now - last_updated) > 900000)
    {
        if (xml_request != null)
        {
            xml_request.abort();
            xml_request = null;
        } // if
        xml_request = new XMLHttpRequest();

        xml_request.onload = function(e) { loaded(e, xml_request); }
        xml_request.overrideMimeType("text/xml");
        xml_request.open("GET", widgeturl);
        xml_request.send(null);
    } // if
} // show

if (window.widget)
    widget.onshow = show;

// end of SaySomethingNice.js ...

