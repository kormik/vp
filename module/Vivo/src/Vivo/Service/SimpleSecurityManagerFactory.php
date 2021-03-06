<?php
namespace Vivo\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * SimpleSecurityManagerFactory
 */
class SimpleSecurityManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\Security\Manager\Simple
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsConfig          = $serviceLocator->get('cms_config');
        $secManConfig       = $cmsConfig['security_manager_simple'];
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEventInterface */
        $siteEvent          = $serviceLocator->get('site_event');

        $secManOptions      = array();
        if($siteEvent->getSite()){
            $secManOptions['security_domain'] = $siteEvent->getSite()
                    ->getDomain();
        }

        $secManOptions      = array_merge($secManOptions, $secManConfig['options']);
        $sessionManager     = $serviceLocator->get('session_manager');
        $securityManager    = new \Vivo\CMS\Security\Manager\Simple($sessionManager, $secManOptions);
        return $securityManager;
    }
}
