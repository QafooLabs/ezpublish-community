<?php

namespace Qafoo\BlogBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;

use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;

class BlogLocationHandler
{
    private $searchService;

    public function __construct($searchService)
    {
        $this->searchService = $searchService;
    }

    public function handle($location, $viewType, $params, $request)
    {
        $params['posts'] = $this->fetchSubTree(
            $location,
            array('blog_post'),
            array(new SortClause\Field('blog_post', 'publication_date', Query::SORT_DESC, 'eng-US')),
            $request->query->get('page', 1),
            1
        );

        return $params;
    }

    protected function fetchSubTree(Location $subTreeLocation, array $typeIdentifiers, array $sortMethods, $page, $limit = 20)
    {
        $criterion = array(
            new Criterion\ContentTypeIdentifier( $typeIdentifiers ),
            new Criterion\Subtree( $subTreeLocation->pathString )
        );

        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd($criterion);

        if ( !empty( $sortMethods ) ) {
            $query->sortClauses = $sortMethods;
        }

        $pager = new Pagerfanta(
            new ContentSearchAdapter( $query, $this->searchService )
        );
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
