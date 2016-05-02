<div class="content admin clr">
	<a href="{SITEURL}admin/pages" style="position: absolute; top: 0; right: 10px; background: #e8e8e8; padding: 5px 10px; text-decoration: none;" title="Go back"><i class="icon-arrow-left"></i></a>

	<form action="{SITEURL}admin/page_add/{page_alias}" method="post">
		<div>Page title:
			<input class="l_standard" type="text" name="page_title" value="{page_title}" placeholder="About 50-80 characters"/></div>

		[isnt_edit]<div>Page url:
			<input class="l_standard" type="text" name="page_alias" value="{page_alias}" placeholder="Up to 10 characters"/></div>[/isnt_edit]
		[is_edit]<input type="hidden" name="page_alias" value="{page_alias}"/>[/is_edit]

		<div class="split_line"><span>SEO parameters</span></div>

		<div>Page description:
			<input class="l_standard" type="text" name="page_description" value="{page_description}" placeholder="About 150-200 characters"/></div>

		<div>Page keywords:
			<input class="l_standard" type="text" name="page_keywords" value="{page_keywords}" placeholder="Up to 250 characters"/></div>

		<div class="split_line"><span>HTML content</span></div>

		<div><textarea class="l_standard" name="page_content" style="height: 150px; margin-bottom: 10px; resize: vertical;">{page_content}</textarea></div>


		[isnt_edit]<input type="hidden" name="type" value="page_add"/>[/isnt_edit]
		[is_edit]<input type="hidden" name="type" value="page_edit"/>[/is_edit]
		<button>Save</button>
	</form>
</div>