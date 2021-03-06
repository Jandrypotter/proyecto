<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\DoctrineCacheBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Cache Bundle Extension
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @author Danilo Cabello <danilo.cabello@gmail.com>
 */
class DoctrineCacheExtension extends Extension
{
    /**
     * @var \Doctrine\Bundle\DoctrineCacheBundle\DependencyInjection\CacheProviderLoader
     */
    private $loader;

    /**
     * @param \Doctrine\Bundle\DoctrineCacheBundle\DependencyInjection\CacheProviderLoader $loader
     */
    public function __construct(CacheProviderLoader $loader = null)
    {
        $this->loader = $loader ?: new CacheProviderLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $rootConfig    = $this->processConfiguration($configuration, $configs);

        $locator = new FileLocator(__DIR__ . '/../Resources/config/');
        $loader  = new XmlFileLoader($container, $locator);

        $loader->load('services.xml');

        $this->loadAcl($rootConfig, $container);
        $this->loadCustomProviders($rootConfig, $container);
        $this->loadCacheProviders($rootConfig, $container);
        $this->loadCacheAliases($rootConfig, $container);
    }

    /**
     * @param array                                                     $rootConfig
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder   $container
     */
    protected function loadAcl(array $rootConfig, ContainerBuilder $container)
    {
        if ( ! isset($rootConfig['acl_cache']['id'])) {
            return;
        }

        if ( ! interface_exists('Symfony\Component\Security\Acl\Model\AclInterface')) {
            throw new \LogicException('You must install symfony/security-acl in order to use the acl_cache functionality.');
        }

        $aclCacheDefinition = new Definition(
            $container->getParameter('doctrine_cache.security.acl.cache.class'),
            array(
                new Reference($rootConfig['acl_cache']['id']),
                new Reference('security.acl.permission_granting_strategy'),
            )
        );

        $aclCacheDefinition->setPublic(false);

        $container->setDefinition('doctrine_cache.security.acl.cache', $aclCacheDefinition);
        $container->setAlias('security.acl.cache', 'doctrine_cache.security.acl.cache');
    }

    /**
     * @param array                                                     $rootConfig
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder   $container
     */
    protected function loadCacheProviders(array $rootConfig, ContainerBuilder $container)
    {
        foreach ($rootConfig['providers'] as $name => $config) {
            $this->loader->loadCacheProvider($name, $config, $container);
        }
    }

    /**
     * @param array                                                     $rootConfig
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder   $container
     */
    protected function loadCacheAliases(array $rootConfig, ContainerBuilder $container)
    {
        foreach ($rootConfig['aliases'] as $alias => $name) {
            $container->setAlias($alias, 'doctrine_cache.providers.' . $name);
        }
    }

    /**
     * @param array                                                     $rootConfig
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder   $container
     */
    protected function loadCustomProviders(array $rootConfig, ContainerBuilder $container)
    {
        foreach ($rootConfig['custom_providers'] as $type => $rootConfig) {
            $providerParameterName   = $this->loader->getCustomProviderParameter($type);
            $definitionParameterName = $this->loader->getCustomDefinitionClassParameter($type);

            $container->setParameter($providerParameterName, $rootConfig['prototype']);

            if ($rootConfig['definition_class']) {
                $container->setParameter($definitionParameterName, $rootConfig['definition_class']);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'doctrine_cache';
    }

    /**
     * {@inheritDoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    /**
     * {@inheritDoc}
     **/
    public function getNamespace()
    {
        return 'http://doctrine-project.org/schemas/symfony-dic/cache';
    }
}
