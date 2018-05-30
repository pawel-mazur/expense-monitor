<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TagFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getData() as $data) {
            $tag = new Tag();
            $tag->setName($data);
            $tag->setUser($this->getReference('user'));

            $w1 = rand(0, ContactFixtures::ROWS);
            $w2 = rand(0, ContactFixtures::ROWS);

            foreach (range(min($w1, $w2), max($w1, $w2), abs($w1 - $w2)) as $contact) {
                $tag->addContact($this->getReference(sprintf('%s.%d', ContactFixtures::PREFIX, $contact)));
            }

            $manager->persist($tag);
        }

        $manager->flush();
    }

    public function getData()
    {
        return [
            'jedzenie',
            'rachunki',
            'rozrywka',
            'wyjazdy',
        ];
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
            ContactFixtures::class,
        ];
    }
}
