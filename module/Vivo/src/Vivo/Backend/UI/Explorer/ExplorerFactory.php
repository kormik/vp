<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\CMS\Api\Manager\Tree;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Explorer factory
 */
class ExplorerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->get('service_manager');
        $siteSelector = $sm->get('Vivo\Backend\UI\SiteSelector');

        $explorer = new \Vivo\CMS\UI\Manager\Explorer\Explorer($sm->get('Vivo\CMS\Api\CMS'),
                $siteSelector);

        $explorer->setEventManager($sm->get('event_manager'));

        $explorer->addComponent($sm->create('Vivo\Backend\UI\Explorer\Ribbon'), 'ribbon');

        //add components
        $explorer->addComponent($sm->create('Vivo\Backend\UI\Explorer\Browser'), 'browser');
        $viewer = new Viewer($sm->get('Vivo\CMS\Api\CMS'));
        $explorer->addComponent($viewer, 'viewer');
        //   $explorer->addComponent($sm->create('Vivo\Backend\UI\Explorer\Viewer'), 'viewer');
        //$explorer->addComponent($sm->create('Vivo\CMS\UI\Manager\Explorer\Editor'), 'editor');

        $explorer->addComponent($sm->create('Vivo\Backend\UI\Explorer\Inspect'), 'inspect');

        $tree = new \Vivo\Backend\UI\Explorer\Tree(
                $sm->get('Vivo\CMS\Api\CMS'),
                $sm->get('Vivo\CMS\Api\Document'));
        $tree->setView($sm->get('view_model'));
        $tree->setEntityManager($explorer);
        $explorer->addComponent($tree, 'tree');

        $finder = new \Vivo\Backend\UI\Explorer\Finder();
        $finder->setAlert($sm->get('Vivo\UI\Alert'));
        $finder->setEntityManager($explorer);
        $finder->setView($sm->get('view_model'));
        $explorer->addComponent($finder, 'finder');

        return $explorer;
    }
}
