<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MainController
{
    /**
    * @Route("/")
    */
    public function index(): Response
    {
        return new Response(
            '<html><body>Hello World!</body></html>'
        );
    }
}
