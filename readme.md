#INSTALLATION

- Setup as virtual host for local development. Add to file httpd-vhosts.conf next few lines:
````apacheconfig
<VirtualHost {sitename}.local:80>
    ServerAdmin serveradmin@root.com
    DocumentRoot "{root folder}"
    ServerName {sitename}.local
    ErrorLog "{root folder}/logs"
    CustomLog "{logs name}.log" common

    AddHandler fcgid-script .php
    FcgidInitialEnv PHPRC "g:/server/php72"
	
	
    <Directory "g:/server/html/BCCRM2/www/">
        AllowOverride All
            order allow,deny
            allow from all
            deny from none
            Require all granted
        <Files ~ "\.php$">
            AddHandler fcgid-script .php
            FcgidWrapper "g:/server/php72/php-cgi.exe" .php
            Options +ExecCGI
            order allow,deny
            allow from all
            deny from none
            Require all granted
        </Files>
    </Directory>
</VirtualHost>

````
- Add file watcher for less file (nmp Node.js)
````
npm install -g less
Argumnt : --source-map=../../public/css/$FileNameWithoutExtension$.css.map --no-color $FileName$ ../../public/css/$FileNameWithoutExtension$.css
Output : $ProjectFileDir$/app/public/css/$FileDirPathFromParent(less)$
````
- Run composer update
```shell script
composer update 
or
composer install

```
## Usage
- Add route web/route.php
```phpt
app()->get|post("pattern", Controller|Clouser, 'method')->name('routename')
```
- Add template
Include haeder - (resource/layots/include/header.php)
```phpt
return view("include.header");
```
## Author
- Zahar Pylypchuck <zacharpu2@gmail.com>
- Kovalychuck Sany <mister.x.2002.06@gmail.com>
- Kylychick Vlad <kulchitskiy.01@gmail.com>
## Licence
