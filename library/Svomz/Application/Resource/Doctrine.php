<?php
use Svomz\Application\Container\DoctrineContainer;

/**
 * Zend Application Resource Doctrine class
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link www.doctrine-project.org
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Svomz_Application_Resource_Doctrine
    extends \Zend_Application_Resource_ResourceAbstract
{
    /**
     * Initializes Doctrine Context.
     *
     * @return Svomz\Application\Container\DoctrineContainer
     */
    public function init()
    {
        $config = $this->getOptions();

        // Bootstrapping Doctrine autoloaders
        $this->registerAutoloaders($config);

        // Starting Doctrine container
        $container = new DoctrineContainer($config);

        // Record the service
        $this->recordService($container, $config);

        return $container;
    }

    /**
     * Register Doctrine autoloaders.
     *
     * The doctrine resource allows user to configure a specific include path
     * for doctrine component. It can be configured in the application config
     * file with the following directive : "resources.doctrine.includePath"
     * @param array Doctrine global configuration
     */
    private function registerAutoloaders(array $config = array())
    {
        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $doctrineIncludePath = isset($config['includePath'])
            ? $config['includePath'] : APPLICATION_PATH . '/../library/Doctrine';

        require_once $doctrineIncludePath . '/Common/ClassLoader.php';

        $symfonyAutoloader = new \Doctrine\Common\ClassLoader('Symfony');
        $autoloader->pushAutoloader(array($symfonyAutoloader, 'loadClass'), 'Symfony');

        $doctrineAutoloader = new \Doctrine\Common\ClassLoader('Doctrine');
        $autoloader->pushAutoloader(array($doctrineAutoloader, 'loadClass'), 'Doctrine');
    }
    /**
     * Save the service in the container
     *
     * The service name can be configured with serviceName directive
     * in the configuration file
     * @param Svomz\Application\Container\DoctrineContainer $container
     */
    private function recordService($container, $config)
    {
        $serviceName = isset($config['serviceName'])
                                        ? $config['serviceName'] : 'doctrine';

        $this->getBootstrap()
                ->getContainer()
                ->setService($serviceName, $container);

    }
}
