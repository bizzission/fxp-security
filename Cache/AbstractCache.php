<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Cache;

use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Base of cache.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractCache implements WarmableInterface
{
    /**
     * @var array
     */
    protected $options = [
        'project_dir' => null,
        'cache_dir' => null,
        'debug' => false,
        'resource_prefixes' => ['config', 'src'],
    ];

    /**
     * @var null|ConfigCacheFactoryInterface
     */
    protected $configCacheFactory;

    /**
     * @var ResourceInterface[]
     */
    protected $resources = [];

    /**
     * Constructor.
     *
     * @param array $options An array of options
     */
    public function __construct(array $options = [])
    {
        $resourcePrefixes = $this->options['resource_prefixes'];
        $this->options = array_merge($this->options, $options);
        $this->options['resource_prefixes'] = array_merge($resourcePrefixes, $this->options['resource_prefixes']);
        $this->options['resource_prefixes'] = array_unique($this->options['resource_prefixes']);

        if (null !== ($projectDir = $this->options['project_dir'])
            && null !== ($prefixes = $this->options['resource_prefixes'])) {
            foreach ($prefixes as $prefix) {
                $path = $projectDir.'/'.$prefix;

                if (file_exists($path)) {
                    $this->resources[] = new DirectoryResource($path, '/\.(php|xml|yaml|yml)$/');
                }
            }
        }
    }

    /**
     * Set the config cache factory.
     *
     * @param ConfigCacheFactoryInterface $configCacheFactory The config cache factory
     */
    public function setConfigCacheFactory(ConfigCacheFactoryInterface $configCacheFactory): void
    {
        $this->configCacheFactory = $configCacheFactory;
    }

    /**
     * Provides the ConfigCache factory implementation, falling back to a
     * default implementation if necessary.
     *
     * @return ConfigCacheFactoryInterface
     */
    protected function getConfigCacheFactory(): ConfigCacheFactoryInterface
    {
        if (!$this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->options['debug']);
        }

        return $this->configCacheFactory;
    }

    /**
     * Load the configurations from cache.
     *
     * @param string   $name              The file cache name
     * @param callable $getConfigurations The callable to retrieve the configurations
     *
     * @return array
     */
    protected function loadConfigurationFromCache($name, callable $getConfigurations): array
    {
        $cache = $this->getConfigCacheFactory()->cache(
            $this->options['cache_dir'].'/'.$name.'_configs.php',
            function (ConfigCacheInterface $cache) use ($getConfigurations): void {
                $configs = $getConfigurations();
                $content = sprintf(
                    'unserialize(%s)',
                    var_export(serialize($configs), true)
                );

                $cache->write($this->getContent($content), $this->resources);
            }
        );

        return require $cache->getPath();
    }

    /**
     * @param string $content The content
     *
     * @return string
     */
    protected function getContent(string $content): string
    {
        return sprintf(
            <<<'EOF'
<?php

return %s;

EOF
            ,
            $content
        );
    }
}
