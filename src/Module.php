<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/17/2017
 * Time: 22:59
 */
namespace Biblio;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Form\FormElementManagerFactory;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\BiblioTable::class => function($container) {
                    $bibEnriesTableGateway = $container->get(Model\BibEntriesTableGateway::class);
                    $bibAuthCrTableGateway = $container->get(Model\BibAuthCrTableGateway::class);
                    $authorTable = $container->get(Model\AuthorTable::class);
                    $authorCrossTG = $container->get(Model\AuthorCrossTableGateway::class);
                    return new Model\BiblioTable($bibEnriesTableGateway, $bibAuthCrTableGateway, $authorTable, $authorCrossTG/*, $referencesTableGateway*/);
                },
                Model\AuthorTable::class => function($container) {
                    $authorsTableGateway = $container->get(Model\AuthorsTableGateway::class);
                    return new Model\AuthorTable($authorsTableGateway);
                },
                Model\ReferencesTable::class => function($container) {
                    $referencesTableGateway = $container->get(Model\ReferencesTableGateway::class);
                    $biblioTable = $container->get(Model\BiblioTable::class);
                    return new Model\ReferencesTable($referencesTableGateway, $biblioTable);
                },
                Model\BibAuthCrTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Biblio());
                    return new TableGateway('biblio_author_cross', $dbAdapter, null, $resultSetPrototype);
                },
                Model\AuthorCrossTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('biblio_author_cross', $dbAdapter);
                },
                Model\AuthorsTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Author());
                    return new TableGateway('authors', $dbAdapter, null, $resultSetPrototype);
                },
                Model\BibEntriesTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Biblio());
                    return new TableGateway('bibliog_entries', $dbAdapter, null, $resultSetPrototype);
                },
                Model\ReferencesTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Reference());
                    return new TableGateway('references', $dbAdapter, null, $resultSetPrototype);
                },
                FormElementManager::class => FormElementManagerFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\BiblioController::class => function($container) {
                    return new Controller\BiblioController(
                        $container->get(Model\BiblioTable::class),
                        $container->get(Model\AuthorTable::class),
                        $container->get(Model\ReferencesTable::class)
                    );
                },
            ],
        ];
    }
}