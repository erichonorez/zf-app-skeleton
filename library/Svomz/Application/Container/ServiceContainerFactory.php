<?php
namespace Svomz\Application\Container;

use Svomz\Application\Exception\FileTypeNotSupportedException;
/**
 * Service container fabric
 *
 * This fabric try to build a Symfony Dependency Injection Component based
 * on an array of parameters.
 */
class ServiceContainerFactory
{
    /**
     * Static property
     * @var sfServiceContainer
     */
    protected static $_container;
    /**
     * Get the container for files given in option.
     *
     * Multiple config options could be set but actually only 'configFiles' is
     * used as an array. The factory will try to load all files in this array.
     * @param array $options
     * @return sfServiceContainer
     */
    public static function getContainer(array $options)
    {
        self::$_container = new \sfServiceContainerBuilder();
        foreach($options['configFiles'] as $file) {
            self::_loadConfigFile($file);
        }

        return self::$_container;
    }
    /**
     * Load dependency injection configuration based on $file in parameter.
     *
     * Configuration files can be Xml / Yaml or Ini file
     * @param  string $file
     * @return void
     * @throws Svomz\Application\Exception\FileTypeNotSupportedException
     * @todo load the section of the configuration relative to the APPLICATION_ENV
     */
    protected static function _loadConfigFile($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'xml':
                $loader = new \sfServiceContainerLoaderFileXml(self::$_container);
                break;

            case 'yml':
                $loader = new \sfServiceContainerLoaderFileYaml(self::$_container);
                break;

            case 'ini':
                $loader = new \sfServiceContainerLoaderFileIni(self::$_container);
                break;

            default:
                throw new FileTypeNotSupportedException(
                        "Invalid configuration file provided; unknown config type '$suffix'");
        }
        $loader->load($file);
    }
}