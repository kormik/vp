<?php
namespace Vivo\SiteManager;

use Vivo\SiteManager\Event\SiteEventInterface;
use Vivo\SiteManager\Exception;
use Vivo\SiteManager\Listener\SiteModelLoadListener;
use Vivo\SiteManager\Listener\SiteConfigListener;
use Vivo\SiteManager\Listener\LoadModulesListener;
use Vivo\SiteManager\Listener\CollectModulesListener;
use Vivo\Module\ModuleManagerFactory;
use Vivo\Module\StorageManager\StorageManager as ModuleStorageManager;
use Vivo\CMS\CMS;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

/**
 * SiteManager
 */
class SiteManager implements SiteManagerInterface,
                             EventManagerAwareInterface
{
    /**
     * Event manager
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * SiteEvent token
     * @var SiteEventInterface
     */
    protected $siteEvent;

    /**
     * Module manager
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * RouteMatch object
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Name of the route param containing the host name
     * @var string
     */
    protected $routeParamHost;

    /**
     * Module manager factory
     * @var ModuleManagerFactory
     */
    protected $moduleManagerFactory;

    /**
     * List of names of core modules (loaded for all sites)
     * @var array
     */
    protected $coreModules    = array();

    /**
     * Module Storage Manager
     * @var ModuleStorageManager
     */
    protected $moduleStorageManager;

    /**
     * CMS object
     * @var CMS
     */
    protected $cms;

    /**
     * Application's service manager
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Constructor
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param Event\SiteEventInterface $siteEvent
     * @param string $routeParamHost Name of route parameter containing the host name
     * @param \Vivo\Module\ModuleManagerFactory $moduleManagerFactory
     * @param array $coreModules
     * @param \Vivo\Module\StorageManager\StorageManager $moduleStorageManager
     * @param \Vivo\CMS\CMS $cms
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     */
    public function __construct(EventManagerInterface $events,
                                SiteEventInterface $siteEvent,
                                $routeParamHost,
                                ModuleManagerFactory $moduleManagerFactory,
                                array $coreModules,
                                ModuleStorageManager $moduleStorageManager,
                                CMS $cms,
                                ServiceManager $serviceManager,
                                RouteMatch $routeMatch = null)
    {
        $this->setEventManager($events);
        $this->siteEvent            = $siteEvent;
        $this->routeParamHost       = $routeParamHost;
        $this->moduleManagerFactory = $moduleManagerFactory;
        $this->coreModules          = $coreModules;
        $this->moduleStorageManager = $moduleStorageManager;
        $this->cms                  = $cms;
        $this->serviceManager       = $serviceManager;
        $this->setRouteMatch($routeMatch);
    }

    /**
     * Bootstraps the SiteManager
     */
    public function bootstrap()
    {
        $this->siteEvent->setTarget($this);
        $this->siteEvent->setRouteMatch($this->routeMatch);

        //Attach Site model load listener
        $configListener         = new SiteModelLoadListener($this->routeParamHost, $this->cms);
        $configListener->attach($this->events);
        //Attach Site config listener
        $configListener         = new SiteConfigListener($this->cms);
        $configListener->attach($this->events);
        //Attach Collect modules listener
        $collectModulesListener = new CollectModulesListener($this->coreModules, $this->moduleStorageManager);
        $collectModulesListener->attach($this->events);
        //Attach Load modules listener
        $loadModulesListener    = new LoadModulesListener($this->moduleManagerFactory, $this->serviceManager);
        $loadModulesListener->attach($this->events);
    }

    /**
     * Prepares the site
     */
    public function prepareSite()
    {
        //Trigger events
        //Load the Site model
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_SITE_MODEL_LOAD, $this->siteEvent);
        //Get Site config
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_CONFIG, $this->siteEvent);
        //Get module names loaded for the site
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_COLLECT_MODULES, $this->siteEvent);
        //Load site modules
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_LOAD_MODULES, $this->siteEvent);
    }

    /**
     * Inject an EventManager instance
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events   = $eventManager;
    }

    /**
     * Retrieve the event manager
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Sets the RouteMatch object
     * @param \Zend\Mvc\Router\RouteMatch|null $routeMatch
     */
    public function setRouteMatch(RouteMatch $routeMatch = null)
    {
        $this->routeMatch = $routeMatch;
    }
}