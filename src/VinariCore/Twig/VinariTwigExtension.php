<?php

namespace VinariCore\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class VinariTwigExtension extends Twig_Extension
{

    public function getName()
    {
        return 'vinari_twig_extension';
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('get_class', 'get_class'),
        ];
    }

    public function getTests()
    {
        return [
            // ...
        ];
    }

    public function getFunctions()
    {
        return [
            // ...
        ];
    }

}
