# BaseProject
Micro Framework BaseProject

## Sommaire
1. Installation
2. Développement
    1. Fonctionnement générale
    2. Controller
    3. Block
    4. Template
    5. Collection / Model
    6. Helper
    7. Router
    8. Évènements
    9. Module
    10. Surcharge
    
## 1. Installation
### 1. Prérequis
TODO:  version php, composer, mysql, redis (cache)
### 2. Apache
```apacheconfig
# Replace {domain} by your domain
<VirtualHost *:80>
        ServerName {domain}
        DocumentRoot /var/www/{domain}/app/
        
        ErrorLog ${APACHE_LOG_DIR}/error-{domain}.log
        CustomLog ${APACHE_LOG_DIR}/access-{domain}.log combined
        
        <IfModule mod_rewrite.c>
                RewriteEngine on
                RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
                RewriteRule .* /index.php
        </IfModule>
        
        <Directory />
                Options +FollowSymLinks
                Require all granted
        </Directory>
        
        <Directory /var/www/{domain}>
                DirectoryIndex index.php
                Options -Indexes +FollowSymLinks
                AllowOverride None
                Require all granted
                allow from all
        </Directory>
        
        ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
        <Directory /usr/lib/cgi-bin>
                AllowOverride None
                Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
                Require all denied
        </Directory>
        
</VirtualHost>
```
### 3. Création de la base de données
Afin que l'application puisse créer ses tables, il faut créer une base de données.
### 4. Configuration de l'application
Pour configurer l'application, il suffit d'ouvrir le navigateur sur l'adresse.
A ce moment là, un wizard vous permet de configurer l'application facilement.

La création des tables se fera suite à la validation du formulaire. 

## 2. Développement
### 1. Fonctionnement générale
TODO: Arborescence, url, création d'un projet (ne pas modifier les classes existantes)
### 2. Controller
TODO: controller principale (Index) méthode principale (indexAction), les méthodes xxxAction

Une classe controller hérite de la classe Controller.
Elle se trouve dans le namespace "NomProject\Module\Controller" 

Le controller par défaut d'un module est Index, la méthode appeler par défaut est indexAction
```php
<?php
class Index extends \App\libs\App\Controller
{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/index.phtml');
        $this->setTemplateHeader('/header/menu.phtml');
        $this->setTemplateFooter('/header/menu.phtml');
        $this->setTitle('Index Controller');
        
        $this->setKey('ABC');
        $this->setUseCache(true);
    } 
}
```
### 3. Block

Une classe block hérite de la classe Block
Elle se trouve dans le namespace "NomProject\Module\Block" 
```php
<?php
class Menu extends \App\libs\App\Block
{    
    /**
     * Menu constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/header/menu/menu.phtml');
        
        $this->setUseCache(true);
        $this->setKey('ABC');
    }
}
```
### 4. Template
Tous les templates se trouvent dans le dossier:
```
/app/design/template/NomProjet/Module
```
L'extension d'un template est .phtml ou .php

Si le template est appelé depuis un controller alors le $this correspond à ce controller.

Si le template est appelé depuis un block alors le $this correspond à ce block

### 5. Collection / Model
Les collections et les models sont liés. Ils correspondent à l'image de la base de données.
Ces classes permettent de modifier/ajouter/supprimer des données.

Une classe collection hérite de la classe App\libs\App\CollectionDb.
Elle se trouve dans le namespace "NomProject\Module\Collection"
Elles sont instanciées de la manière suivante :

```php
<?php
$collection = App\libs\App\Collection::getInstance('Module_NomCollection'); 
```

Une classe Model hérite de la classe App\libs\App\ModelDb.
Elle se trouve dans le namespace "NomProject\Module\Model" 
 
```php
<?php
$model = App\libs\App\Model::getInstance('Module_NomModel'); 
```
### 6. Helper
Une classe helper hérite de la classe App\libs\App\Helper.
Elle se trouve dans le namespace "NomProject\Module\Helper"

Elles sont instanciées de la manière suivante :

```php
<?php
$helper = App\libs\App\Helper::getInstance('Module_NomHelper'); 
```
### 7. Router
TODO: obligatoire, surcharge getRoute()
La classe Router est obligatoire dans tous les modules. Elle hérite toujours de la Classe App\libs\App\Router.
```php
<?php

class Router extends \App\libs\App\Router
{
    
}
```
Elle se trouve dans le namespace "NomProjet\Module\Router"

Il est possible de surcharger la méthode getRoute() afin de créer vos propre routes.

### 8. Evènements
TODO: Observer / Dispatcher
### 9. Module
TODO: création d'un nouveau module, fichier de configuration etc/config.json (toutes les possibilités)
### 10. Surcharge
TODO: Comment créer la surcharge d'une classe 