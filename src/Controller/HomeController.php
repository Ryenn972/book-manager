<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, BookRepository $bookRepository): Response
    {
        $search = $request->query->get('q');
        $genre = $request->query->get('genre');

        $queryBuilder = $bookRepository->createQueryBuilder('b');

        if ($search) {
            $queryBuilder->andWhere('b.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($genre) {
            $queryBuilder->andWhere('b.genre = :genre')
                ->setParameter('genre', $genre);
        }

        $books = $queryBuilder->getQuery()->getResult();

        return $this->render('home/index.html.twig', [
            'books' => $books,
            'search' => $search,
            'genre' => $genre,
        ]);
    }

}
