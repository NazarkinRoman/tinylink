<div class="content admin clr">
	<a href="{SITEURL}admin" style="position: absolute; top: 0; right: 50px; background: #e8e8e8; padding: 5px 10px; text-decoration: none;" title="Go back"><i class="icon-arrow-left"></i></a>
	<a href="{SITEURL}admin/page_add" style="position: absolute; top: 0; right: 10px; background: #e8e8e8; padding: 5px 10px; text-decoration: none;" title="Go back"><i class="icon-plus"></i></a>

	<h1 style="font-weight: normal; font-family: 'Roboto', sans-serif; margin: 10px 0 15px 0;">Static Pages</h1>

	<table class="linksFlow">
		<thead><tr>
			<td width="60%">Page title</td>
			<td width="20%" class="small canhide">Date created</td>
			<td width="20%" class="small">Actions</td>
		</tr></thead>

		<tbody>
		{pagesflow}
		</tbody>
	</table>

	[no_pages]<div class="content clr">
		<div style="margin: 50px 0; text-align: center;">
			<i class="icon-warning-sign icon-3x" style="vertical-align: middle; margin-right: 10px"></i> There is no pages for now.
		</div>
	</div>[/no_pages]

</div>