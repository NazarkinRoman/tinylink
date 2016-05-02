            <tr id="link_{link_alias}">
                <td><a href="{link_short}+">
                        [has_title]{link_page_title}[/has_title]
                        [hasnt_title]{link_url_crop}[/hasnt_title]
                    </a></td>
                <td class="small canhide">{create_date}</td>
                <td class="small actions">
                    <a href="{SITEURL}delete/{link_alias}" onclick="deleteLink('{link_alias}'); return false;">Delete</a>
                    / <a href="{link_url}" target="_blank">Open</a></td>
            </tr>