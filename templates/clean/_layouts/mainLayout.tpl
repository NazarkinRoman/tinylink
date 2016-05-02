<!DOCTYPE html>
<html>
<head>
    <title>{TITLE}</title>

    <meta charset="utf-8"/>
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- SEO parameters -->
    <meta name="keywords" content="{KEYWORDS}" />
    <meta name="description" content="{DESCRIPTION}" />

    <link rel="shortcut icon" href="{THEME}/_static/favicon.ico" />

    <link rel="stylesheet" href="{THEME}/_static/css/main.css"/>
    <link rel="stylesheet" href="{THEME}/_static/css/responsive.css"/>
    <noscript><link rel="stylesheet" href="{THEME}/_static/css/nojs.css"/></noscript>
    <link rel="stylesheet" href="{THEME}/_static/css/vendor/font-awesome.min.css">
    <!--[if IE 7]>
    <link rel="stylesheet" href="{THEME}/_static/css/vendor/font-awesome-ie7.min.css">
    <![endif]-->
</head>

<body>
<script type="text/javascript">
    var SITEURL = '{SITEURL}';
    var CONTROLLER = '{CONTROLLER}';
    var ACTION = '{ACTION}';
</script>

<!-- Logo -->
<h1 class="l_logo {CONTROLLER}_{ACTION}"><a href="{SITEURL}">TinyLink</a></h1>

<!-- Main Container -->
<div class="l_wrap clr animated fadeInDown {CONTROLLER}_{ACTION}" id="content_wrap">
    {container:content}
</div>

<div class="l_wrap wrap_btn clr animated fadeInDown {CONTROLLER}_{ACTION}">
	<a href="/my">My Links <i class="icon-arrow-right"></i></a>
</div>

[flashmessage]<!-- Flash Messages -->
<div class="flash_message animated fadeInRight">
    {flashmessage}
</div>
[/flashmessage]

<!-- Init Jquery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="{THEME}/_static/js/vendor/jquery-1.7.1.min.js"><\/script>')</script>
<script src="{THEME}/_static/js/vendor/jquery.cookie.js"></script>
<script src="{THEME}/_static/js/vendor/jquery.core-ui-select.js"></script>
<script src="{THEME}/_static/js/main.js"></script>

</body>
</html>