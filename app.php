<?php

use App\App;
use App\Config;
use App\libs\App\Helper;
use BaseProject\Admin\Helper\Admin;
use BaseProject\Admin\Helper\Cache;

$mode_cli = (php_sapi_name() == 'cli');

if (!$mode_cli) {
    // Web interface
    if (isset($params['install'])):
        $_currentConfig = Config::getInstance();
        if (isset($_POST['save'])) {
            $config = array_replace_recursive($_currentConfig->getConfig(), ['app' => $_POST['app']],
                ['mysql' => $_POST['mysql']], ['redis' => $_POST['redis']]);
            $config['app']['installed'] = 1;
            $_currentConfig->setConfig($config);

            $db = new \App\Db();
            $db->upgradeDb();

            /** @var Cache $cacheHelper */
            $cacheHelper = Helper::getInstance('Admin_Cache');
            $cacheHelper->clearCache();

            header('Location: /');
        } else {
            ?>
            <!doctype html>
            <html>
            <head>
                <title>Install application</title>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="stylesheet" href="/assets/css/style.css"/>
                <link rel="stylesheet" href="/assets/components/bootstrap/dist/css/bootstrap.min.css"/>
                <script src="/assets/components/jquery/dist/jquery.min.js"></script>
                <script type="application/javascript" src="/assets/components/bootstrap/dist/js/bootstrap.min.js"></script>
                <style type="text/css">
                    body {
                        background-color: #f8f8f8;
                    }

                    h1 {
                        font-size: 48px;
                        font-weight: 200;
                        text-align: center;
                        color: #525252;
                        font-family: "Source Sans Pro", sans-serif;
                        margin: 2.67em 0;
                    }

                    button {
                        width: 100%;
                    }

                    .margin-bottom-50 {
                        margin-bottom: 50px;
                    }
                </style>
            </head>
            <body>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <h1>Welcome to the BaseProject installation</h1>
                        <form action="/" method="post">
                            <input type="hidden" name="save" value="1"/>
                            <fieldset>
                                <legend>General</legend>
                                <div class="form-group">
                                    <label for="app_debug">Debugger</label>
                                    <select id="app_debug" name="app[debug]" class="form-control">
                                        <option value=""></option>
                                        <option value="1" <?= ($_currentConfig->getAttribute('app',
                                                'debug') == true) ? 'selected' : '' ?> >
                                            Yes
                                        </option>
                                        <option value="0" <?= ($_currentConfig->getAttribute('app',
                                                'debug') != true) ? 'selected' : '' ?> >
                                            No
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="app_enabledCache">Enable cache</label>
                                    <select id="app_enabledCache" name="app[enabledCache]" class="form-control">
                                        <option value=""></option>
                                        <option value="1" <?= ($_currentConfig->getAttribute('app',
                                                'enabledCache') == true) ? 'selected' : '' ?> >
                                            Yes
                                        </option>
                                        <option value="0" <?= ($_currentConfig->getAttribute('app',
                                                'enabledCache') != true) ? 'selected' : '' ?> >
                                            No
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="app_secure">Secure</label>
                                    <select id="app_secure" name="app[secure]" class="form-control">
                                        <option value=""></option>
                                        <option value="1" <?= ($_currentConfig->getAttribute('app',
                                                'secure') == true) ? 'selected' : '' ?> >
                                            Yes
                                        </option>
                                        <option value="0" <?= ($_currentConfig->getAttribute('app',
                                                'secure') != true) ? 'selected' : '' ?> >
                                            No
                                        </option>
                                    </select>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>Database</legend>

                                <div class="form-group">
                                    <label for="mysql_type">Type</label>
                                    <select id="mysql_type" name="mysql[type]" class="form-control">
                                        <option value="mysql">Mysql</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="mysql_host">Host</label>
                                    <input id="mysql_host" type="text" name="mysql[host]"
                                           value="<?= $_currentConfig->getAttribute('mysql', 'host'); ?>"
                                           class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label for="mysql_dbname">Database name</label>
                                    <input id="mysql_dbname" type="text" name="mysql[dbname]"
                                           value="<?= $_currentConfig->getAttribute('mysql', 'dbname'); ?>"
                                           class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label for="mysql_user">User</label>
                                    <input id="mysql_user" type="text" name="mysql[user]"
                                           value="<?= $_currentConfig->getAttribute('mysql', 'user'); ?>"
                                           class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label for="mysql_pass">Password</label>
                                    <input id="mysql_pass" type="password" name="mysql[pass]"
                                           value="<?= $_currentConfig->getAttribute('mysql', 'pass'); ?>"
                                           class="form-control"/>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>Redis</legend>
                                <div class="form-group">
                                    <label for="redis_host">Host</label>
                                    <input id="redis_host" type="text" name="redis[host]"
                                           value="<?= $_currentConfig->getAttribute('redis', 'host'); ?>"
                                           class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label for="redis_port">Port</label>
                                    <input id="redis_port" type="number" name="redis[port]"
                                           value="<?= $_currentConfig->getAttribute('redis', 'port'); ?>"
                                           class="form-control"/>
                                </div>
                            </fieldset>
                            <div class="row margin-bottom-50">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary">Install</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </body>
            </html>
            <?php
        }
    endif;

    if (isset($params['maintenance'])):
        ?>
        Maintenance !!
        <?php
    endif;
} else {
    // Command line
    include_once "vendor/autoload.php";
    $GLOBALS['override'] = json_decode(file_get_contents(__DIR__ . '/app/etc/override.json'), true);
    App::getInstance()->setPathRoot(__DIR__ . '/app');
    App::getInstance()->init();

    $params = getopt('', ['upgrade', 'generate-db', 'module:', 'state:', 'module-list', 'help', 'maintenance:', 'refresh-cache', 'generate']);

    if (isset($params['upgrade'])) {
        $db = new \App\Db();
        echo "\nStart upgrade Db\n";
        $db->upgradeDb();
        echo "End upgrade Db\n";
    } else if (isset($params['generate-db'])) {

        $db = new \App\Db();
        echo "\nStart generate upgrade Db\n";
        try {
            $db->generateSqlDbModification();
        } catch (Exception $ex) {
            echo $ex->getMessage()."\n";
        }
        echo "End generate upgrade Db\n";

    } else if (isset($params['module-list'])) {
        $modules = \App\libs\App\CollectionDb::getInstanceOf('Admin_Module')->loadAll(['module_name ASC']);

        echo "\nList of modules :\n";
        /** @var \BaseProject\Admin\Model\Module $module */
        foreach ($modules as $module) {
            echo $module->getAttribute('module_name') . " : " . (($module->getAttribute('enable')) ? 'is active' : 'is disabled') . "\n";
        }
    } else if (isset($params['maintenance'])) {
        $_currentConfig = Config::getInstance();
        $config = $_currentConfig->getConfig();
        $config['app']['maintenance'] = $params['maintenance'];
        $_currentConfig->setConfig($config);

        echo (($params['maintenance']) ? 'Maintenance is active' : 'Maintenance is disabled') . "\n";
    } else if (isset($params['module'], $params['state'])) {

        $module = \App\libs\App\CollectionDb::getInstanceOf('Admin_Module')->load(['module_name' => $params['module']])->getFirstRow();
        /** @var Admin $adminHelper */
        $adminHelper = Helper::getInstance('Admin_Admin');
        if ($module) {
            if ($params['state'] == '1') {
                $adminHelper->enableModule($module);
                echo "Module enabled\n";
            } else {
                $adminHelper->disableModule($module);
                echo "Module disabled\n";
            }
        } else {
            echo "Module does't exist\n";
        }
    } else if (isset($params['refresh-cache'])) {
        /** @var Cache $cacheHelper */
        $cacheHelper = Helper::getInstance('Admin_Cache');
        $cacheHelper->clearCache();

        echo "Cache has refreshed\n";
    } else if (isset($params['help'])) {
        echo "app.php --help\n\n";
        echo "    --upgrade         to start script upgrade db version\n";
        echo "    --generate-db     generate Sql modification\n";
        echo "    --module-list     list module name with state\n";
        echo "    --module          module name\n";
        echo "    --state           state to active (1) or disable (0) module (with --module)\n";
        echo "    --maintenance     active (1) disable (0)\n";
        echo "    --refresh-cache   refresh cache\n";
        echo "\n";
    } else if (isset($params['generate'])) {
        $type = readline('Generate ? (module/block/model/collection/helper/controller) : ');

        $projectName = readline("Project name : ");
        $moduleName = readline("Module name : ");
        $path = App::PathRoot().'/code';

        switch ($type) {
            case 'module':
                if (!file_exists($path."/".$projectName)) {
                    mkdir($path."/".$projectName);
                    mkdir($path."/".$projectName."/".$moduleName);
                } else if (!file_exists($path."/".$projectName."/".$moduleName)) {
                    mkdir($path."/".$projectName."/".$moduleName);
                }

                mkdir($path."/".$projectName."/".$moduleName."/Controller");
                mkdir($path."/".$projectName."/".$moduleName."/etc");
                mkdir($path."/".$projectName."/".$moduleName."/Router");

                $config = ["router" => ["rewriter_uri" => []]];
                file_put_contents($path."/".$projectName."/".$moduleName."/etc/config.json", json_encode($config));

                $routerContent = <<<EOF
<?php

namespace ::namespace::\Router;

class Router extends \App\libs\App\Router
{

} 
EOF;
                $routerContent = str_replace("::namespace::", $projectName."\\".$moduleName, $routerContent);
                $controllerContent = <<<EOF
<?php

namespace ::namespace::\Controller;

use App\libs\App\Controller;

class Index extends Controller
{
    
}
EOF;
                $controllerContent = str_replace("::namespace::", $projectName."\\".$moduleName, $controllerContent);
                file_put_contents($path."/".$projectName."/".$moduleName."/Controller/Index.php", $controllerContent);
                file_put_contents($path."/".$projectName."/".$moduleName."/Router/Router.php", $routerContent);

                $modulesConfig = json_decode(file_get_contents(App::PathRoot().'/etc/modules.json'), true);
                if (!in_array($moduleName, $modulesConfig)) {
                    $modulesConfig[] = $moduleName;
                    file_put_contents(App::PathRoot().'/etc/modules.json', json_encode($modulesConfig));
                }
                $overrideConfig = json_decode(file_get_contents(App::PathRoot().'/etc/override.json'), true);
                if (!in_array($projectName, $overrideConfig)) {
                    $overrideConfig[] = $projectName;
                    file_put_contents(App::PathRoot().'/etc/override.json', json_encode($overrideConfig));
                }

                break;
            case 'block':
                $name = readline("Block name : ");
                if (!file_exists($path."/".$projectName."/".$moduleName."/Block")) {
                    mkdir($path."/".$projectName."/".$moduleName."/Block");
                }
                $content = <<<EOF
<?php

namespace ::namespace::\Block;

use App\libs\App\Block;

class ::name:: extends Block
{
    
}
EOF;
                $content = str_replace("::namespace::", $projectName."\\".$moduleName, $content);
                $content = str_replace("::name::", $name, $content);
                file_put_contents($path."/".$projectName."/".$moduleName."/Block/".$name.".php", $content);
                break;
            case 'model':
            case 'collection':
                $name = readline("Model name : ");
                $tableName = readline("Table name : ");
                $tableKey = readline("Table key : ");

                $etcConfig = json_decode(file_get_contents($path."/".$projectName."/".$moduleName."/etc/config.json"), true);

                if(!isset($etcConfig['tables'])) {
                    $etcConfig['tables'] = [];
                }

                $etcConfig['tables'][] = [
                    "model" => $projectName."\\".$moduleName."\\Model\\".$name,
                    "collection" => $projectName."\\".$moduleName."\\Collection\\".$name,
                    "table_name" => $tableName,
                    "key" => $tableKey
                ];

                file_put_contents($path."/".$projectName."/".$moduleName."/etc/config.json", json_encode($etcConfig));

                if (!file_exists($path."/".$projectName."/".$moduleName."/Model")) {
                    mkdir($path."/".$projectName."/".$moduleName."/Model");
                }
                $content = <<<EOF
<?php

namespace ::namespace::\Model;

use App\libs\App\ModelDb;

class ::name:: extends ModelDb
{
    
}
EOF;
                $content = str_replace("::namespace::", $projectName."\\".$moduleName, $content);
                $content = str_replace("::name::", $name, $content);
                file_put_contents($path."/".$projectName."/".$moduleName."/Model/".$name.".php", $content);

                $name = readline("Collection name : ");
                if (!file_exists($path."/".$projectName."/".$moduleName."/Collection")) {
                    mkdir($path."/".$projectName."/".$moduleName."/Collection");
                }
                $content = <<<EOF
<?php

namespace ::namespace::\Collection;

use App\libs\App\CollectionDb;

class ::name:: extends CollectionDb
{
    
}
EOF;
                $content = str_replace("::namespace::", $projectName."\\".$moduleName, $content);
                $content = str_replace("::name::", $name, $content);
                file_put_contents($path."/".$projectName."/".$moduleName."/Collection/".$name.".php", $content);
                break;
            case 'helper':
                $name = readline("Helper name : ");
                if (!file_exists($path."/".$projectName."/".$moduleName."/Helper")) {
                    mkdir($path."/".$projectName."/".$moduleName."/Helper");
                }
                $content = <<<EOF
<?php

namespace ::namespace::\Helper;

use App\libs\App\Helper;

class ::name:: extends Helper
{
    
}
EOF;
                $content = str_replace("::namespace::", $projectName."\\".$moduleName, $content);
                $content = str_replace("::name::", $name, $content);
                file_put_contents($path."/".$projectName."/".$moduleName."/Helper/".$name.".php", $content);
                break;
            case 'controller':
                $name = readline("Controller name : ");
                if (!file_exists($path."/".$projectName."/".$moduleName."/Controller")) {
                    mkdir($path."/".$projectName."/".$moduleName."/Controller");
                }
                $content = <<<EOF
<?php

namespace ::namespace::\Controller;

use App\libs\App\Controller;

class ::name:: extends Controller
{
    
}
EOF;
                $content = str_replace("::namespace::", $projectName."\\".$moduleName, $content);
                $content = str_replace("::name::", $name, $content);
                file_put_contents($path."/".$projectName."/".$moduleName."/Controller/".$name.".php", $content);
                break;
            default:
                echo "Command unknown\n\n";
        }
    }
}