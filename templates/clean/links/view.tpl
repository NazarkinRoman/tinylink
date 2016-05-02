<div class="l_input_group clr" id="main_input_group">
    <input type="text" id="short_url" class="l_main input" value="{link_short}" readonly/>
    <button class="l_main button" id="copy_button" data-clipboard-target="short_url" title="Copy to clipboard"><i class="icon-copy"></i></button>
    <button class="l_main button link" data-href="{SITEURL}" title="Short another URL"><i class="icon-reply"></i></button>
</div>

<div class="content clr">
    [without_password]<img src="http://free.pagepeeker.com/v2/thumbs.php?size=l&url={link_url}" id="site_screenshot" />
    <script type="text/javascript">
        var screenURL = 'http://free.pagepeeker.com/v2/thumbs.php?rndv=' + new Date().getTime() + '&size=l&url={link_url}';
        var qrcodeURL = 'http://api.qrserver.com/v1/create-qr-code/?data={link_url}&size=400x400&margin=0';
        var preloaderURL = '{THEME}/_static/img/preloader.gif';

        document.getElementById('site_screenshot').src = preloaderURL;
        setTimeout(function() { document.getElementById('site_screenshot').src = screenURL;}, 10000);
    </script>

    <div class="site_info clr">[has_description]{link_page_description}[/has_description]
        [hasnt_description]<i class="descr_not_available">Description is not available!</i>[/hasnt_description]
        <div><b>Target URL:</b> <a href="{link_url}" style="word-break: break-all;">{link_url_crop}</a></div>
        [is_author]<div><b>Visits:</b> {link_visits}</div>[/is_author]
    </div>

    <div class="button_container clr">
        <a href="{link_url}" class="big_button gotosite" target="_blank">Go to URL</a>
        [is_author]<a href="{SITEURL}delete/{link_alias}" class="big_button deletelink" title="Delete link" id="delete_link">
            <i class="icon-remove"></i></a>[/is_author]
    </div>[/without_password]

    [with_password]<div class="password_protect">
        <div class="password_wrap">
            <form action="" method="POST">
                <span style="vertical-align: super; margin-right: 5px;">Enter password:</span>
                <span class="bordered_input"><input type="password" name="password" class="l_standard password"
                            placeholder="...and hit Enter" />
                [wrong_password]<div class="l_hint">Wrong password!</div>[/wrong_password]</span>
            </form>
        </div>
    </div>[/with_password]

</div>

<script type="text/javascript" src="{THEME}/_static/js/vendor/ZeroClipboard.js"></script>
<script type="text/javascript">
    ZeroClipboard.setDefaults( {
        moviePath: '{THEME}/_static/js/vendor/ZeroClipboard.swf',
        hoverClass: 'hover',
        activeClass: 'active'
    } );

    var clip = new ZeroClipboard( document.getElementById('copy_button') );
    clip.on( 'noflash wrongflash', function(client) {
        document.getElementById('copy_button').style.display = 'none';
        document.getElementById('main_input_group').className  += ' compact';
    } );

    clip.on( 'complete', function(client, args) {
        $('#short_url').showMessage('Link copied!', false, false);
        setTimeout(
                function() { $('#short_url').hideMessage(); },
                1500
        );
    } );
</script>