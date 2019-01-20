<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class TestRoute
{
    public static function test()
    {
        $routes = new RouteCollection();

        $routes->add('blog_list', new Route('/blog', array(
            '_controller' => '_matched_blog_list'
        )));

        $routes->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => '_matched_blog_show'
        )));

        $context = new RequestContext('access_maint');

        $matcher = new UrlMatcher($routes, $context);

        $parameters = $matcher->match('/blog');
        var_dump(Request::createFromGlobals());
        die();
    }
}
