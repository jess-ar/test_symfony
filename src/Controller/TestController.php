<?php

namespace App\Controller;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestController extends AbstractController
{
    private EntityManagerInterface $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}