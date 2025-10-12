<?php

namespace Webberdoo\InstallerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstallerController extends AbstractController
{
    #[Route('/install', name: 'installer_index')]
    public function index(): Response
    {
        return $this->render('@Installer/installer.html.twig');
    }
}
