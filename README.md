![TinyLink mini icon](http://s2.micp.ru/T93y9.png)

# TinyLink
Small and beautiful URL shortening engine built on PHP. 

# System Requirements
For correct system work, on your server must be installed: 
- **PHP** >= 5.3
- **Apache** (with **mod_rewrite**)

# Installation
Copy all system files to your server through FTP or from Control Panel of your hosting provider and go to admin panel(http://yoursite.com/admin), log in with this data: 
- login: **admin**
- password: **123**
then, go to the page - http://yoursite.com/admin/settings and change settings to yours. **System installed!** All the rest will do script program, there is nothing else necessary from you. 

# Customization
All template files are stored in `/templates/*template_name*/` (default theme named **clean**).
In the templates is used Smarty-similar marking, that why you will not have to correct any PHP code. Only HTML. 

## Available templates
- `/_layouts/mainLayout.tpl` - general page layout, all the rest are exposed in it 
- `/_layouts/error.tpl` - error page template 
- `/_layouts/redirectPage.tpl` - template for link redirect page(if enabled in settings)  
- `/_layouts/admin.tpl` - general layout for admin panel (not recommend to edit) 
- `/_static/` - here stored all CSS, JS files and also images 
- `/index/index.tpl` - general(index) page template  
- `/links/view.tpl` - template for link page (<http://tinylink.nazarkin.su/pfy+>)  
- `/links/my.tpl` - layout for user links page 
- `/links/link.single.tpl` - template for a single link in a user links page 
- `/admin/index.tpl` - general page of admin panel 
- `/admin/settings.tpl` - settings page template 
- `/admin/login.tpl` - template for login page 
- `/admin/link.single.tpl` - template for single link from a links list 

## Template variables 

**Global variables, works inside each template file:**
- `{TITLE}`, `{KEYWORDS}`, `{DESCRIPTION}` - SEO meta tags 
- `{THEME}` - path to theme folder 
- `{SITEURL}` - site url with end slash 
- `{SITE_DOMAIN}` - site domain without any slashes 
- `{ACTION}`, `{CONTROLLER}` - service variables 
- `{container:content}` - container for body of page
- `[flashmessage]{flashmessage}[/flashmessage]` - block for display system messages 

**`/_layouts/redirectPage.tpl`:**
- `[without_password] .. [/without_password]` - content of this block will only displayed for links without password protection 
- `[with_password] .. [/with_password]` - only for links with password protection  
- `[wrong_password] .. [/wrong_password]` - if entered password is incorrect 
- `{full_url}` - original URL

**`/_layouts/error.tpl`:**
- `{errorMessage}` - system error message

**`view.tpl` and `link.single.tpl`:**
- password blocks as in redirect page template 
- `{link_short}` - shorten link 
- `{link_url}` - full link 
- `{link_url_crop}` - full link cropped to 70 symbols 
- `{link_alias}` - short link code 
- `{link_visits}` - count of link visits 
- `{create_date}` - link creation date 
- `[has_title] {link_page_title} [/has_title]` - link page title(from meta tag). Displayed only if
available 
- `[hasnt_title] .. [/hasnt_title]` - iа title is not available 
- `[has_description] {link_page_description} [/has_description]` - block contains link
description and displays only if description is available 
- `[hasnt_description] [/hasnt_description]` - only if description is not available 
- `[is_author] .. [/is_author]` - displayed only for author of this link 

**`/links/my.tpl`:**
- `{linksflow}` - list of links (generated from `link.single.tpl` template) 
- `[has_pagination] {pagination} [/has_pagination]` - displayed only if there are more then two pages 

**`/admin/index.tpl`:**
- `{linksflow}` - list of links (generated from `link.single.tpl` template) 
- `{linksCount}` - count of all links shortened through engine 
- `{linksToday}` - count of links shortened today 
- `[no_links] .. [/no_links]` - displayed if there is no links 
- `[has_pagination] {pagination} [/has_pagination]` - displayed only if there are more then two pages 

# API Subsystem
This engine also have a simple **REST-like API system**. All responses is in **JSON** format.

To short link, you need to send `POST` request to `http://yoursite.com/api/short` with next parameters: 
- url - link to short. *String.*
- alias - custom alias for this url. *String.* 
- password - password to protect url. *String.* 
- lifetime - link lifetime. *Integer.* Allowed values: 
    - 1 - ten minutes
    - 2 - one hour 
    - 3 - three hours 
    - 4 - one day 
    - 5 - one week
    - 6 - one month 

**Example response:**
```json
{  
   "status":"success",
   "data":{  
      "url":"https:\/\/github.com\/Vestride\/Shuffle",
      "alias":"pYD",
      "password":null,
      "lifetime":"0",
      "page_description":"Shuffle - Categorize, sort, and filter a responsive grid of items",
      "page_title":"GitHub - Vestride\/Shuffle: Categorize, sort, and filter a responsive grid of items",
      "visits":0,
      "create_date":1462205128,
      "author":"c47d80480ec94c168a1754a797fe167315e153cbbc6159d5816bbfd44716be50de8ff99e57a3366a"
   }
}
```

To delete shorten link, send `GET` request to `http://yoursite.com/api/delete/<linkAlias>`  
View all info about shorten link - `GET` request to `http://yoursite.com/api/<linkAlias>`