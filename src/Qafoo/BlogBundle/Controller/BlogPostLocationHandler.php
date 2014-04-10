<?php

namespace Qafoo\BlogBundle\Controller;

class BlogPostLocationHandler
{
    public function handle($location, $viewType, $params, $request)
    {
        $params['comments'] = array( array('text' => 'HAllo'), array('text' => 'World!') );
        return $params;
    }
}
