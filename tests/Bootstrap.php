<?php
namespace PlaygroundGameTest;

use Laminas\Loader\AutoloaderFactory;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use RuntimeException;

class Bootstrap
{
    protected static $serviceManager;
    protected static $config;
    protected static $bootstrap;

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        chdir(__DIR__);
        // Load the user-defined test configuration file, if it exists; otherwise, load
        if (is_readable(__DIR__ . '/TestConfig.php')) {
            $testConfig = include __DIR__ . '/TestConfig.php';
        } else {
            $testConfig = include __DIR__ . '/TestConfig.php.dist';
        }

        $zf3ModulePaths = array();

        if (isset($testConfig['module_listener_options']['module_paths'])) {
            $modulePaths = $testConfig['module_listener_options']['module_paths'];
            foreach ($modulePaths as $modulePath) {
                if (($path = static::findParentPath($modulePath))) {
                    $zf3ModulePaths[] = $path;
                }
            }
        }

        $zf3ModulePaths  = implode(PATH_SEPARATOR, $zf3ModulePaths) . PATH_SEPARATOR;
        $zf3ModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ?: (defined('ZF2_MODULES_TEST_PATHS')?
            ZF2_MODULES_TEST_PATHS :
            ''
        );

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $baseConfig = array(
            'module_listener_options' => array(
                'module_paths' => explode(PATH_SEPARATOR, $zf3ModulePaths),
            ),
        );

        $config = ArrayUtils::merge($baseConfig, $testConfig);

        $smConfig = isset($config['service_manager']) ? $config['service_manager'] : [];
        $smConfig = new ServiceManagerConfig($smConfig);
        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);

        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
        static::$config = $config;

        // disable FirePHP for Unit testing
        //$firephp = \FirePHP::getInstance(true);
        //$firephp->setEnabled(false);
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getConfig()
    {
        return static::$config;
    }



    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            $zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ?
                ZF2_PATH :
                (is_dir($vendorPath . '/ZF2/library') ? $vendorPath . '/ZF2/library' : false));

            if (!$zf2Path) {
                throw new RuntimeException(
                    'Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.'
                );
            }

            include $zf2Path . '/Laminas/Loader/AutoloaderFactory.php';
        }

        AutoloaderFactory::factory(array(
            'Laminas\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}

Bootstrap::init();

// let's avoid session_start(): Cannot send session cookie - headers already sent by
ob_start();
