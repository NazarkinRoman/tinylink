<form action="{SITEURL}short" method="POST" id="link_post">
    <div class="l_input_group clr">
        <input type="text" name="url" id="url" class="l_main input" placeholder="Type your URL here..." autofocus maxlength="500" />
        <button class="l_main button" id="submit_button" title="Short!"><i class="icon-signin"></i></button>
        <button class="l_main button" id="config_link" title="Settings"><i class="icon-cogs"></i></button>
    </div>

    <div class="content hide clr" id="config_params">
        <div class="l_col">
            <h6>Custom URL</h6>

            <div class="l_standard staticPlaceholder">
                <div><label for="alias" style="cursor: text;">{SITE_DOMAIN}/</label></div>
                <div><input type="text" name="alias" id="alias" maxlength="15" /></div>
            </div>
        </div>

        <div class="l_col">
            <h6>Password</h6>
            <input type="password" name="password" class="l_standard" placeholder="optional" maxlength="30" />
        </div>

        <div class="l_col">
            <h6>Link expiration</h6>
            <select name="lifetime" class="l_standard">
                <option selected value="0">Never</option>
                <option value="1">10 minutes</option>
                <option value="2">1 hour</option>
                <option value="3">3 hours</option>
                <option value="4">1 day</option>
                <option value="5">1 week</option>
                <option value="6">1 month</option>
            </select>
        </div>
    </div>
</form>