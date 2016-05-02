<div class="content admin clr">
    <div class="l_col bigint">
        <h6 class="pad_bottom">All links</h6>

        <div style="font-size: 20px;">{linksCount}</div>
    </div>

    <div class="l_col bigint">
        <h6 class="pad_bottom">Today shortened</h6>

		<div style="font-size: 20px;">{linksToday}</div>
    </div>

    <div class="l_col">
        <h6>Controls</h6>

		<div class="l_col" style="width: 60%;">
        <div><i class="icon-wrench"></i> <a href="{SITEURL}admin/settings/">Settings</a></div>
        <div><i class="icon-file"></i> <a href="{SITEURL}admin/pages/">Static Pages</a></div>
		</div>
        <div><i class="icon-external-link"></i> <a href="{SITEURL}admin/logout/">Logout</a></div>

		<div class="clr"></div>
    </div>
</div>

<table class="linksFlow">
    <thead><tr>
        <td width="60%">Link title</td>
        <td width="20%" class="small canhide">Date created</td>
        <td width="20%" class="small">Actions</td>
    </tr></thead>

    <tbody>
{linksflow}
    </tbody>
</table>

[no_links]<div class="content clr">
    <div style="margin: 50px 0; text-align: center;">
        <i class="icon-warning-sign icon-3x" style="vertical-align: middle; margin-right: 10px"></i> There is no links! :(
    </div>
</div>[/no_links]

[has_pagination]<div class="pagination">{pagination}</div>[/has_pagination]