<?php

namespace Qafoo\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;

use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;

class BlogController extends ViewController
{
    private $typeHandlers = array();

    public function addTypeHandler($contentTypeId, $handler)
    {
        $this->typeHandlers[$contentTypeId] = $handler;
    }

    private function getHandler($contentTypeId)
    {
        return $this->typeHandlers[$contentTypeId];
    }

    public function viewLocation( $locationId, $viewType, $layout = false, array $params = array(), Request $request = null )
    {
        $locationService = $this->getRepository()->getLocationService();
        $location = $locationService->loadLocation( $locationId );
        $modificationDate = $location->contentInfo->modificationDate;

        $handler = $this->getHandler($location->contentInfo->contentTypeId);
        $params = $handler->handle($location, $viewType, $params, $request);

        $response = $this->container->get( 'ez_content' )->viewLocation( $locationId, $viewType, $layout, $params );
        $response->setETag($location->contentInfo->contentTypeId . '-'. $locationId);
        $response->setLastModified($modificationDate);

        return $response;
    }
}
