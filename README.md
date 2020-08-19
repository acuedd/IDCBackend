GUÍA DE INSTALACIÓN
===================

* Genius
* Version 3.0.1
* Created by **Homeland S.A.**
* Thanks to: 
    * Edward Acu 
    * Fernando Ajú
    * Nelson Matul 
    * Jason Hernández
    
### Who do I talk to? ###
    
* Edward Acu <eacu@homeland.com.gt>
* Alejandro Gudiel <agudiel@homeland.com.gt>
 
# Instalación

Instalar docker según el sistema operativo y seguir las indicaciones: 
 
* [Docker for Mac](https://docs.docker.com/docker-for-mac/)
* [Docker for Windows](https://docs.docker.com/docker-for-windows/)
* [Docker for Ubuntu](https://linuxize.com/post/how-to-install-and-use-docker-compose-on-ubuntu-18-04/)

Una vez instalado docker revisar tener instalado docker-compose: 

```
 [root@centos]# docker-compose --V
 docker-compose version 1.23.2, build 1110ad01
  
```
Una vez instalado correr el siguiente comando.  

```
[root@centos]# docker-compose up -d
genius2_mariadb_1 is up-to-date
Starting genius2_app_1 ... done

```

**Nota:** Si se desea modificar el nombre de la base de datos default y los puertos donde se quiere escuchar los servicios
entonces modificar el archivo .env que se encuentra en el directorio raíz, ahí encontrá la configuración default de la siguiente 
manera (es importante no dejar espacios en blanco en las últimas versiones de docker): 
```
DATABASE_NAME=<YOUR_DB_NAME>
DATABASE_USER=<YOUR_USER>
DATABASE_PASSWORD=<YOUR_PASS>
DATABASE_ROOT_PASSWORD=<YOUR_PASS>
HTTP_PORT=80
HTTPS_PORT=443
LOCAL_USER=root:root
```

Para revisar el estado de los contanedores: 
```
[root@centos]#  docker ps 
CONTAINER ID        IMAGE                   COMMAND                  CREATED             STATUS              PORTS                    NAMES
61658bd18009        acuedd/httpd:composer   "/usr/sbin/init"         28 hours ago        Up 53 seconds       0.0.0.0:80->80/tcp       genius2_webapp_1
c6849d53b92d        mariadb:10.1            "docker-entrypoint.s?"   28 hours ago        Up 4 hours          0.0.0.0:3306->3306/tcp   genius2_mariadb_1
```

**Nota:** Revisar el archivo docker-compose.yml para mayor información sobre el servicio de base de datos. 

Ingresar al contendor del webapp, por lo que ingresará al bash del contenerdor: 
```
[root@centos]#  docker-compose exec webapp bash
[root@4e827f24d2f9 /]# 

```

El contenedor que inicia es CentOS 7, por lo que se podrá realizar cualquier tarea como en la terminal de CentOS 7.
 
Verificar que el servicio httpd este activo: 
```
[root@4e827f24d2f9 /]# systemctl status httpd.service
? httpd.service - The Apache HTTP Server
   Loaded: loaded (/usr/lib/systemd/system/httpd.service; enabled; vendor preset: disabled)
   Active: active (running) since Fri 2019-03-29 21:54:01 UTC; 2min 17s ago
     Docs: man:httpd(8)
           man:apachectl(8)
 Main PID: 177 (httpd)
   Status: "Total requests: 0; Current requests/sec: 0; Current traffic:   0 B/sec"
   CGroup: /docker/4e827f24d2f983dbced53c73e55a11f76eb6f724078c3b2b7121bef68f5724dc/system.slice/httpd.service
           ??177 /usr/sbin/httpd -DFOREGROUND
           ??489 /usr/sbin/httpd -DFOREGROUND
           ??491 /usr/sbin/httpd -DFOREGROUND
           ??492 /usr/sbin/httpd -DFOREGROUND
           ??496 /usr/sbin/httpd -DFOREGROUND
           ??497 /usr/sbin/httpd -DFOREGROUND

Mar 29 21:54:00 4e827f24d2f9 systemd[1]: Starting The Apache HTTP Server...
Mar 29 21:54:01 4e827f24d2f9 httpd[177]: AH00558: httpd: Could not reliably determine the server's fully qualified domain name, using 172.18.0.3. Set the 'ServerName' directiv...this message
Mar 29 21:54:01 4e827f24d2f9 systemd[1]: Started The Apache HTTP Server.
Hint: Some lines were ellipsized, use -l to show in full.

```

Dirigirse al htdocs y iniciar composer (revisar archivo composer.json para saber más sobre los scripts): 
```
[root@4e827f24d2f9 /]# cd /var/www/html/
[root@4e827f24d2f9 html]# composer start
> composer install
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Package operations: 40 installs, 0 updates, 0 removals
  - Installing cebe/markdown (1.2.1): Downloading (100%)         
    Skipped installation of bin bin/markdown for package cebe/markdown: name conflicts with an existing file
  - Installing symfony/polyfill-mbstring (v1.11.0): Downloading (100%)         
  - Installing psr/log (1.1.0): Downloading (100%)         
  - Installing symfony/debug (v3.0.9): Downloading (100%)         
  - Installing symfony/console (v2.8.49): Downloading (100%)         
  - Installing symfony/polyfill-ctype (v1.11.0): Downloading (100%)         
  - Installing symfony/filesystem (v4.2.4): Downloading (100%)         
  - Installing symfony/config (v4.2.4): Downloading (100%)         
  - Installing davedevelopment/phpmig (v1.5.0): Downloading (100%)         
    Skipped installation of bin bin/phpmig for package davedevelopment/phpmig: name conflicts with an existing file
  - Installing delight-im/file-upload (v1.2.0): Downloading (100%)         
  - Installing ralouphie/getallheaders (2.0.5): Downloading (100%)         
  - Installing psr/http-message (1.0.1): Downloading (100%)         
  - Installing guzzlehttp/psr7 (1.5.2): Downloading (100%)         
  - Installing guzzlehttp/promises (v1.3.1): Downloading (100%)         
  - Installing guzzlehttp/guzzle (6.3.3): Downloading (100%)         
  - Installing symfony/translation (v3.2.14): Downloading (100%)         
  - Installing nesbot/carbon (1.36.2): Downloading (100%)         
  - Installing illuminate/support (v4.1.30): Downloading (100%)         
  - Installing illuminate/container (v4.1.30): Downloading (100%)         
  - Installing illuminate/events (v4.1.30): Downloading (100%)         
  - Installing illuminate/database (v4.1.0): Downloading (100%)         
  - Installing kint-php/kint (2.2): Downloading (100%)         
  - Installing setasign/fpdi (1.6.2): Downloading (100%)         
  - Installing paragonie/random_compat (v9.99.99): Downloading (100%)         
  - Installing myclabs/deep-copy (1.8.1): Downloading (100%)         
  - Installing mpdf/mpdf (v7.1.9): Downloading (100%)         
  - Installing nette/utils (v3.0.0): Downloading (100%)         
  - Installing nette/mail (v2.4.6): Downloading (100%)         
  - Installing phpmailer/phpmailer (v6.0.7): Downloading (100%)         
  - Installing symfony/routing (v3.0.9): Downloading (100%)         
  - Installing symfony/http-foundation (v3.0.9): Downloading (100%)         
  - Installing symfony/event-dispatcher (v3.0.9): Downloading (100%)         
  - Installing symfony/http-kernel (v3.0.9): Downloading (100%)         
  - Installing pimple/pimple (v1.1.1): Downloading (100%)         
  - Installing silex/silex (v1.3.6): Downloading (100%)         
  - Installing symfony/yaml (v2.8.49): Downloading (100%)         
  - Installing phpoption/phpoption (1.5.0): Downloading (100%)         
  - Installing vlucas/phpdotenv (v3.3.3): Downloading (100%)         
  - Installing filp/whoops (1.1.10): Downloading (100%)         
  - Installing symfony/var-dumper (v2.8.49): Downloading (100%)         
symfony/console suggests installing psr/log-implementation (For using the console logger)
symfony/console suggests installing symfony/process
nesbot/carbon suggests installing friendsofphp/php-cs-fixer (Needed for the `composer phpcs` command. Allow to automatically fix code style.)
nesbot/carbon suggests installing phpstan/phpstan (Needed for the `composer phpstan` command. Allow to detect potential errors.)
setasign/fpdi suggests installing setasign/fpdf (FPDI will extend this class but as it is also possible to use "tecnickcom/tcpdf" as an alternative there's no fixed dependency configured.)
setasign/fpdi suggests installing setasign/fpdi-fpdf (Use this package to automatically evaluate dependencies to FPDF.)
setasign/fpdi suggests installing setasign/fpdi-tcpdf (Use this package to automatically evaluate dependencies to TCPDF.)
paragonie/random_compat suggests installing ext-libsodium (Provides a modern crypto API that can be used to generate random bytes.)
mpdf/mpdf suggests installing ext-bcmath (Needed for generation of some types of barcodes)
mpdf/mpdf suggests installing ext-xml (Needed mainly for SVG manipulation)
nette/utils suggests installing ext-intl (to use Strings::webalize(), toAscii(), normalize() and compare())
nette/utils suggests installing ext-xml (to use Strings::length() etc. when mbstring is not available)
phpmailer/phpmailer suggests installing hayageek/oauth2-yahoo (Needed for Yahoo XOAUTH2 authentication)
phpmailer/phpmailer suggests installing league/oauth2-google (Needed for Google XOAUTH2 authentication)
phpmailer/phpmailer suggests installing stevenmaguire/oauth2-microsoft (Needed for Microsoft XOAUTH2 authentication)
symfony/routing suggests installing doctrine/annotations (For using the annotation loader)
symfony/routing suggests installing symfony/dependency-injection (For loading routes from a service)
symfony/routing suggests installing symfony/expression-language (For using expression matching)
symfony/event-dispatcher suggests installing symfony/dependency-injection
symfony/http-kernel suggests installing symfony/browser-kit
symfony/http-kernel suggests installing symfony/class-loader
symfony/http-kernel suggests installing symfony/dependency-injection
symfony/http-kernel suggests installing symfony/finder
symfony/var-dumper suggests installing ext-symfony_debug
Package silex/silex is abandoned, you should avoid using it. Use symfony/flex instead.
Generating autoload files
> @php -r "file_exists('.env') || copy('.env.example', '.env'); "
```

Una vez instalado composer, configurar las variables de entorno para conexión con la base de datos (archivo .env).
* las credenciales se encuentran en las variables de entorno del docker-compose.yml, del service mariadb.
   ```
    environment:
           MYSQL_ALLOW_EMPTY_PASSWORD: "no"
           MYSQL_ROOT_PASSWORD: "<YOUR_PASS>"
           MYSQL_USER: 'root'
           MYSQL_PASSWORD: '<YOUR_PASS>'
           MYSQL_DATABASE: '<YOUR_DATABASE>'
   ```
   
* Es necesario colocar como host el nombre del service de base de datos, en este caso mariadb
(es importante no dejar espacios en blanco en las últimas versiones de docker)
```
DEBUG=1
HML_DBTYPE='mysqli'
HML_DATABASE='<YOUR_DATABASE>'
HML_HOST='mariadb'
HML_PREFIX='wt'
HML_USER='root'
HML_PASS='<YOUR_PASS>'
HML_TIMEZONE='America/Guatemala'
HML_ENVIROMENT='tester'
HML_VERSION_APP='2.2.3'
HML_DBENGINE='innodb' 
```

Configurar la base de datos con el siguiente comando:
``` 
[root@4e827f24d2f9 html]# composer refresh-database
> php homeland phpmig:migrate
 == 20170101152901 StartUp migrating
 == 20170101152901 StartUp migrated 0.5873s
[root@4e827f24d2f9 html]#
```

Ingresar en el navegador al localhost y se deberá visualizar la ventana de **No disponible** 

**Nota:** Las configuraciones de tíldes y charset ya se encuentran realizadas en el contenedor pero si aún así persiste 
el error se puede proceder a: 
 
* Si al momento de entrar a la url ya se tiene abierto el IDE con el proyecto y sin embargo devuelve un error 500, revisar
que el encoding del proyecto (en el IDE) este como iso8859-1, descartar cualquier cambio en la branch y reintentar el procedimiento anterior.

* Si la tíldes o ñ no se muestran bien (Para estas opciones se debe reiniciar el servicio httpd): 
  * También se puede revisar el php.ini en el **webapp**, buscar el charset y modificarlo a ISO-8859-1.
  * Revisar en httpd.conf que el charset este de la misma forma como ISO-8859-1

Se recomienda utilizar herramientas como navicat o phpMyAdmin para correr los sql, sin embargo al momento de ejecutarlos 
seleccionar el encoding como iso8859-1 

 
```
[root@4e827f24d2f9 html]# vim /etc/php.ini
 
; PHP's default character set is set to UTF-8.
; http://php.net/default-charset
default_charset = "iso8859-1"

```

```
[root@4e827f24d2f9 html]#  systemctl restart httpd.service
```


#Anotaciones 

##Docker-compose  

###Stop containers
Para detener los contenedores con docker-compose:  
 ```
 [root@centos]# docker-compose stop  
 ```

 Si se trabaja con docker, se puede realizar el siguiente comando: 
```
[root@centos]# docker stop $(docker ps -a) 
``` 
###Down containers
Si se trabaja con docker-compose, se podrá ejecutar el siguiente comando: 
 ```
 [root@centos]# docker-compose down
 ```
 La diferencia entre docker-compose `stop` y `down`, radica en que `stop` simplemente
 detiene el funcionamiento de los contenedores sin eliminar las configuraciones que se hayan 
 realizado dentro, sin embargo, `down` también detiene el funcionamiento pero elimina 
 al contenedor y las redes que se hayan creado. 
 Esto puede provocar que pierdan configuraciones que se hayan realizado en el container. 

###Delete containers 

```
[root@centos]# docker rm $(docker ps -a) 
```

###Logs containers 
```
[root@centos]# docker-compose logs -f <SERVICE>
```
###Delete all volumes 
```
[root@centos]# docker volume prune
``` 

###Delete all images
```
[root@centos]# docker rmi $(docker images -a -q)
``` 
