<?php

namespace Qafoo\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;

class BlogController extends ViewController
{
    public function viewLocation( $locationId, $viewType, $layout = false, array $params = array() )
    {
        $locationService = $this->getRepository()->getLocationService();
        $location = $locationService->loadLocation( $locationId );
        $modificationDate = $location->contentInfo->modificationDate;

        $postResults = $this->fetchSubTree(
            $location,
            array('blog_post'),
            array(new SortClause\Field('blog_post', 'publication_date', Query::SORT_DESC, 'eng-US'))
        );

        $posts = array();
        foreach ( $postResults->searchHits as $hit ) {
            $posts[] = $hit->valueObject;

            if ($hit->valueObject->contentInfo->modificationDate > $modificationDate) {
                $modificationDate = $hit->valueObject->contentInfo->modificationDate;
            }
        }

        $params['posts'] = $posts;

        $response = $this->container->get( 'ez_content' )->viewLocation( $locationId, $viewType, $layout, $params );
        $response->setETag('BlogPostList' . $locationId);
        $response->setLastModified($modificationDate);

        return $response;
    }

    protected function fetchSubTree(Location $subTreeLocation, array $typeIdentifiers, array $sortMethods)
    {
        $searchService = $this->getRepository()->getSearchService();

        $criterion = array(
            new Criterion\ContentTypeIdentifier( $typeIdentifiers ),
            new Criterion\Subtree( $subTreeLocation->pathString )
        );

        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd($criterion);

        if ( !empty( $sortMethods ) ) {
            $query->sortClauses = $sortMethods;
        }
        $query->limit = 20;

        return $searchService->findContent( $query );
    }

}
