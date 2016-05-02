<div class="content admin clr">
	<a href="{SITEURL}admin" style="position: absolute; top: 0; right: 10px; background: #e8e8e8; padding: 5px 10px; text-decoration: none;" title="Go back"><i class="icon-arrow-left"></i></a>

	<div class="l_col big_col">
		<h6 class="pad_bottom">Global settings</h6>

		<form action="{SITEURL}admin/save_settings/" method="post">
			<div>Site URL:
				<input class="l_standard" type="text" name="site_url" value="{site_url}"/></div>

			<div>Theme:
				<select name="theme">
					{themes_list}
				</select>
			</div>


			<!-- SEO params -->
			<div class="split_line"><span>SEO parameters</span></div>

			<div>Site title:
				<input class="l_standard" type="text" name="site_title" value="{site_title}"/></div>

			<div>Site description:
				<input class="l_standard" type="text" name="site_description" value="{site_description}"/></div>

			<div>Site keywords:
				<input class="l_standard" type="text" name="site_keywords" value="{site_keywords}"/></div>


			<!-- Admin panel -->
			<div class="split_line"><span>Admin panel parameters</span></div>

			<div>Admin login:
				<input class="l_standard" type="text" name="admin->login" value="{admin->login}"/></div>

			<div>Admin password:
				<input class="l_standard" type="password" name="admin->password" value="{admin->password}"/></div>


			<!-- Performance settings -->
			<div class="split_line"><span>Performance</span></div>

			<div>Cache method:
				<select name="cache->method" id="cache_type">
					<option value="file"
					[file-cache] selected[/file-cache]>Files</option>
					<option value="memcache"
					[cache-memcache] selected[/cache-memcache]>Memcache</option>
				</select>
			</div>

			<div>Cache enabled:
				<input type="checkbox" name="cache->enabled"[cache-enabled] checked[/cache-enabled]/>
			</div>

			<div class="split_line memcache_coords"><span>Memcache parameters</span></div>

			<div class="memcache_coords">
				Memcache server:
				<input class="l_standard" type="text" name="cache->memcache->server" value="{cache->memcache->server}"/>
			</div>

			<div class="memcache_coords">
				Memcache port:
				<input class="l_standard" type="text" name="cache->memcache->port" value="{cache->memcache->port}"/>
			</div>

			<input type="hidden" name="type" value="global"/>
			<button>Save</button>
		</form>
	</div>

	<!-- Right column -->
	<div class="l_col big_col">
		<h6 class="pad_bottom">Domain black-list</h6>

		<form action="{SITEURL}admin/save_settings/" method="post" style="margin-bottom: 20px;">
			<textarea name="black-list" class="l_standard" style="height: 150px; margin-bottom: 10px; resize: vertical;">{blacklist_items}</textarea>

			<input type="hidden" name="type" value="black_list"/>
			<button>Save</button>
		</form>

		<h6 class="pad_bottom">About</h6>

		<img src="http://s2.micp.ru/F9f4k.png" style="vertical-align: text-top;"/> Из России с любовью ;) </br> &copy; <a href="http://codecanyon.net/user/TrickyMilk" target="_blank">TrickyMilk</a>,
		2013
	</div>
</div>

<script type="text/javascript">
	var cacheTypeSelect = document.getElementById('cache_type');

	function getElementsByClassName(classname, node) {
		if (!node) node = document.getElementsByTagName('body')[0];
		var a = [];
		var re = new RegExp('\\b' + classname + '\\b');
		var els = node.getElementsByTagName('*');
		for (var i = 0, j = els.length; i < j; i++)
			if (re.test(els[i].className))a.push(els[i]);
		return a;
	}

	cacheTypeSelect.onchange = function () {
		var elements = getElementsByClassName('memcache_coords');

		if (cacheTypeSelect.value == 'memcache') {
			for (i in elements)
				elements[i].style.display = 'block';
		} else {
			for (i in elements)
				elements[i].style.display = 'none';
		}
	}

	cacheTypeSelect.onchange();
</script>