<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('user@user.pl');
        $user->setEmail('user@user.pl');
        $user->setPlainPassword('user');
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
    }
}
