<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --- Créer un utilisateur admin ---
        $admin = new User();
        $admin->setEmail('admin@demo.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $manager->persist($admin);

        // --- Créer un utilisateur simple ---
        $user = new User();
        $user->setEmail('user@demo.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
        $manager->persist($user);

        // --- Créer des livres ---
        for ($i = 1; $i <= 10; $i++) {
            $book = new Book();
            $book->setTitle("Livre $i");
            $book->setAuthor("Auteur $i");
            $book->setDescription("Ceci est la description du livre $i.");
            $book->setGenre("Roman");
            $book->setCreatedAt(new \DateTimeImmutable());
            $book->setUpdatedAt(new \DateTime());
            $book->setOwner($user); // ou $admin si tu veux que ça appartienne à l’admin
            $manager->persist($book);
        }

        $manager->flush();
    }
}
