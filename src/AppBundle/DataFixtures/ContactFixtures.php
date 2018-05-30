<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ContactFixtures extends Fixture implements DependentFixtureInterface
{
    const PREFIX = 'contact';

    const ROWS = 20;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (range(0, self::ROWS) as $key => $data) {
            $contact = new Contact();
            $contact->setName(sprintf('Kontakt %d', $key));
            $contact->setUser($this->getReference('user'));

            $this->setReference(sprintf('%s.%s', self::PREFIX, $key), $contact);
            $manager->persist($contact);
        }

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
