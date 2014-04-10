<?php

namespace Qafoo\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class BlogController extends ViewController
{
    public function viewLocation( $locationId, $viewType, $layout = false, array $params = array(), Request $request = null )
    {
        $locationService = $this->getRepository()->getLocationService();
        $location = $locationService->loadLocation( $locationId );
        $modificationDate = $location->contentInfo->modificationDate;

        $contentTypeService = $this->getRepository()->getContentTypeService();
        $contentType = $contentTypeService->loadContentType(
            $location->contentInfo->contentTypeId
        );
        $method = "handle" . ucfirst($contentType->identifier);

        if (!method_exists($this, $method)) {
            throw new \RuntimeException("Cannot render " . $contentType->identifier);
        }

        $params = $this->$method($location, $viewType, $params, $request);

        $response = $this->container->get( 'ez_content' )->viewLocation( $locationId, $viewType, $layout, $params );
        $response->setETag($location->contentInfo->contentTypeId . '-'. $locationId);
        $response->setLastModified($modificationDate);

        return $response;
    }

    public function handleBlog($location, $viewType, $params, $request)
    {
        $contentService = $this->container->get('qafoo_blog.content_service');
        $params['posts'] = $contentService->fetchSubTree(
            $location,
            array('blog_post'),
            array(new SortClause\Field('blog_post', 'publication_date', Query::SORT_DESC, 'eng-US')),
            $request->query->get('page', 1),
            1
        );

        return $params;
    }

    public function handleBlog_Post($location, $viewType, $params, $request)
    {
        $params['comments'] = array( array('text' => 'HAllo'), array('text' => 'World!') );
        return $params;
    }
}
