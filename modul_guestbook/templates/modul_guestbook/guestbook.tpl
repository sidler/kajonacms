<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: link_newentry, link_back, link_pages, link_forward, liste_posts -->
<list>
    <div class="guestbook">
        %%link_newentry%%<br />
        <p>%%link_back%% %%link_pages%% %%link_forward%%</p>
        <div class="posts">%%liste_posts%%</div>
    </div>
</list>

<!-- available placeholders: post_name, post_name_plain, post_email, post_page, post_text, post_date -->
<post>
    <table>
        <tr>
            <td>%%lang_post_name_from%%: %%post_name_plain%%</td>
            <td style="text-align: right;">%%post_date%%</td>
        </tr>
        <tr>
            <td colspan="2">%%lang_post_page_text%%: %%post_page%%</td>
        </tr>
        <tr>
            <td>%%lang_post_message_text%%:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">%%post_text%%</td>
        </tr>
    </table>
</post>

<!-- available placeholders: eintragen_fehler, gb_post_name, gb_post_email, gb_post_text, gb_post_page, action -->
<entry_form>
    <ul>%%eintragen_fehler%%</ul>
    <form name="form1" method="post" action="%%action%%" accept-charset="UTF-8">
        <div><label for="gb_post_name">%%lang_post_name_text%%*:</label><input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="inputText" /></div><br />
        <div><label for="gb_post_email">%%lang_post_mail_text%%*:</label><input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="inputText" /></div><br />
        <div><label for="gb_post_page">%%lang_post_page_text%%:</label><input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="inputText" /></div><br />
        <div><label for="gb_post_text">%%lang_post_message_text%%*:</label><textarea name="gb_post_text" id="gb_post_text" class="inputTextarea">%%gb_post_text%%</textarea></div><br /><br />
        <div><label for="kajonaCaptcha_gb"></label><span id="kajonaCaptcha_gb"><script type="text/javascript">KAJONA.portal.loadCaptcha('gb', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('gb', 180); return false;">%%lang_post_code_text%%</a>)</div><br />
    	<div><label for="gb_post_captcha">Code*:</label><input type="text" name="gb_post_captcha" id="gb_post_captcha" class="inputText" /></div><br /><br />
    	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_post_submit_text%%" class="button" /></div><br />
    </form>
</entry_form>

<!-- available placeholders: error -->
<error_row>
    <li>%%error%%</li>
</error_row>