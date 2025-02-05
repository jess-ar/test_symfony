<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;



final class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_posts')]
    public function list(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('post/list.html.twig', ['posts' => $posts]);
    }


    #[Route('/post/new', name: 'post_new')]
    public function new(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($user);
    
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo subir la imagen.');
                    return $this->redirectToRoute('post_new');
                }
    
                $post->setImage($newFilename);
            }
    
            $em->persist($post);
            $em->flush();
    
            $this->addFlash('success', 'Post creado con éxito.');
            return $this->redirectToRoute('app_posts');
        }
    
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }    



    #[Route('/post/{id}/edit', name: 'post_edit')]
    public function edit(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo subir la imagen.');
                }
            }
    
            $em->flush();
            $this->addFlash('success', 'Post actualizado correctamente.');
    
            return $this->redirectToRoute('app_posts', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
    

    #[Route('/post/{id}/delete', name: 'post_delete')]
    public function delete(Post $post, EntityManagerInterface $em): Response
    {
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('app_posts');
    }
}