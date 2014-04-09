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
    public function postsAction($parentLocationId, $viewType = 'summary')
    {
        $locationService = $this->getRepository()->getLocationService();
        $root = $locationService->loadLocation( $parentLocationId );
        $modificationDate = $root->contentInfo->modificationDate;

        $postResults = $this->fetchSubTree(
            $root,
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

        $response = $this->buildResponse('BlogPosts' . $parentLocationId, $modificationDate);

        if ( $response->isNotModified( $this->getRequest() ) ) {
            return $response;
        }

        return $this->render(
            'QafooBlogBundle::posts_list.html.twig',
            array( 'posts' => $posts, 'viewType' => $viewType ),
            $response
        );
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
