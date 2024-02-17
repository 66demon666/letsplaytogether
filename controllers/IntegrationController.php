<?php

namespace controllers;

use classes\core\Application;
use classes\core\Controller;

class IntegrationController extends Controller
{
    public function confirmation($object): void
    {
            echo Application::CONFIRMATION_TOKEN;
    }
}