<!DOCTYPE html>
<html>
<head>
    <title>{TITLE}</title>

    <meta charset="utf-8"/>
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>

    <link rel="shortcut icon" href="{THEME}/_static/favicon.ico"/>
    <link rel="stylesheet" href="{THEME}/_static/css/main.css"/>
    <link rel="stylesheet" href="{THEME}/_static/css/redirect_page.css"/>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="{THEME}/_static/js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
</head>

<body>

[without_password]
<script>
    var calcHeight = function () {
        var headerDimensions = $('#l_top_block').outerHeight(true);
        $('#main_frame').height($(window).height() - headerDimensions);
    }

    $(document).ready(function () {
        calcHeight();
    });

    $(window).resize(function () {calcHeight()}).load(function () {calcHeight()});
</script>

<div id="l_top_block" class="clr">
    <a href="{SITEURL}" class="logo">TinyLink</a>
    <img src="http://s2.micp.ru/527xy.gif"/>
    <a href="{full_url}" class="button" style="float: right; margin-right: 15px;">Go to URL</a>
</div>

<iframe src="{full_url}" id="main_frame">Your browser doesn't support IFrames</iframe>
[/without_password]

[with_password]
<div class="password_wrap">
    <form action="" method="POST">
        <span style="vertical-align: super; margin-right: 5px;">Enter password:</span>
                <span class="bordered_input"><input type="password" name="password" class="l_standard password"
                                                    placeholder="...and hit Enter"/>
                [wrong_password]<div class="l_hint">Wrong password!</div>[/wrong_password]</span>
    </form>
</div>
[/with_password]

</body>
</html>