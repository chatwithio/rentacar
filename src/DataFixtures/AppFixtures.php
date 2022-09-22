<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('hamza@email.com');
        
        $password = $this->hasher->hashPassword($user, 'pass_1234');
        $user->setPassword($password);

        for ($i = 0; $i < 30; $i++) {
            $message = new Message();
            $message->setDelivered(true);
            $message->setMessageType('message');
            $message->setMessageTo('34622814642');
            $message->setMessageFrom('gBGGNGIoFGQvAgnKhIs0aCgHfo8');
            $message->setMessageContent('Demo');
            $manager->persist($message);
        }
        
        $manager->persist($user);

        $manager->flush();
    }
}