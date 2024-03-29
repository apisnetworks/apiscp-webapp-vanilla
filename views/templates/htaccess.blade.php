<IfModule mod_rewrite.c>
	RewriteEngine On

	####
	# Certain hosts may require the following line.
	# If vanilla is in a subfolder then you need to specify it after the /.
	# (ex. You put Vanilla in /forum so change the next line to: RewriteBase /forum)
	####
	#RewriteBase /

	# Make sure that / doesn't try to go to index.php without a rewrite :)
	DirectoryIndex disabled

	####
	# Deny access to certain directories that SHOULD NOT be exposed.
	####
	RewriteRule (^|/)\.git - [L,R=403]
	RewriteRule ^cache/ - [L,R=403]
	RewriteRule ^cgi-bin/ - [L,R=403]
	RewriteRule ^uploads/import/ - [L,R=403]
	RewriteRule ^vendor/ - [L,R=403]

	####
	# Prevent access to any php script by redirecting the request to /index.php
	# You can add an exception by adding another RewriteCond after this one.
	# Example: RewriteCond %{REQUEST_URI} !^/yourscriptname.php$
	# You can comment out this section if it causes you problems.
	# This is just a nice to have for security purposes.
	####
	RewriteCond %{REQUEST_URI} !/index.php$
	RewriteRule (.+\.php) [E=X_REWRITE:1,E=X_PATH_INFO:/$1,L]

	####
	# Redirect any non existing file/directory to /index.php
	####
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule (.*) index.php [E=X_REWRITE:1,E=X_PATH_INFO:/$1,L]

	####
	# Add the proper X_REWRITE server variable for rewritten requests.
	####
	RewriteCond %{ENV:REDIRECT_X_REWRITE} .+
	RewriteCond %{ENV:REDIRECT_X_PATH_INFO} (.+)
	RewriteRule ^index\.php - [E=X_REWRITE:1,E=!REDIRECT_X_REWRITE,E=X_PATH_INFO:%1,E=!REDIRECT_X_PATH_INFO,L]

	<IfModule mod_setenvif.c>
		####
		# Pass Authorization header to php environment variable to support API authentication
		####
		SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
	</IfModule>
</IfModule>

<IfModule mod_headers.c>
	<FilesMatch "(?<!embed)\.(css|js|woff|ttf|eot|svg|png|gif|jpeg|jpg|ico|swf)$">
		Header set Cache-Control "max-age=315360000"
		Header set Expires "31 December 2037 23:59:59 GMT"
	</FilesMatch>
</IfModule>