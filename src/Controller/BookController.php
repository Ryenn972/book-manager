<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/book')]
final class BookController extends AbstractController
{
    #[Route(name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $book->setOwner($this->getUser());
        $book->setCreatedAt(new \DateTimeImmutable());
        $book->setUpdatedAt(new \DateTime());

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('coverImage')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('covers_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gérer l’erreur si nécessaire
                }

                $book->setCoverImage($newFilename);
            }

            // Générer le slug
            $book->setSlug($slugger->slug($book->getTitle()));

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Livre créé avec succès !');

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // Vérification de la sécurité : seul le propriétaire ou un admin peut éditer
        if ($this->getUser() !== $book->getOwner() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n’avez pas le droit de modifier ce livre.');
            return $this->redirectToRoute('app_book_index');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('coverImage')->getData();

            if ($imageFile) {
                // Supprimer l’ancien fichier si existant
                if ($book->getCoverImage()) {
                    $oldFile = $this->getParameter('covers_directory').'/'.$book->getCoverImage();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Générer un nouveau nom de fichier sécurisé
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('covers_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                    return $this->redirectToRoute('app_book_edit', ['id' => $book->getId()]);
                }

                $book->setCoverImage($newFilename);
            }

            // Mettre à jour la date de modification
            $book->setUpdatedAt(new \DateTime());

            // Mettre à jour le slug si le titre a changé
            $book->setSlug($slugger->slug($book->getTitle()));

            $em->flush();

            $this->addFlash('success', 'Livre modifié avec succès !');
            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }


    #[Route('/books/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        // Vérification de la sécurité
        if ($this->getUser() !== $book->getOwner() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n’avez pas le droit de supprimer ce livre.');
            return $this->redirectToRoute('app_book_index');
        }

        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            // Supprimer l’image associée si elle existe
            if ($book->getCoverImage()) {
                $filePath = $this->getParameter('covers_directory').'/'.$book->getCoverImage();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $em->remove($book);
            $em->flush();

            $this->addFlash('success', 'Livre supprimé avec succès.');
        }

        return $this->redirectToRoute('app_book_index');
    }

}
