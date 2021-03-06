<?php
namespace Vivo\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * SimpleSecurityManagerFactory
 */
class DbSecurityManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\Security\Manager\Simple
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsConfig              = $serviceLocator->get('cms_config');
        $sessionManager         = $serviceLocator->get('session_manager');
        $dbTableGatewayProvider = $serviceLocator->get('db_table_gateway_provider');
        if (isset($cmsConfig['security_manager_db']['options'])) {
            $options    = $cmsConfig['security_manager_db']['options'];
        } else {
            $options    = array();
        }
        $remoteAddress          = $_SERVER['REMOTE_ADDR'];
        $securityManager        = new \Vivo\CMS\Security\Manager\Db($sessionManager, $dbTableGatewayProvider,
                                                                    $remoteAddress, $options);
        return $securityManager;
    }
}
