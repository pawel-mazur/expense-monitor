<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Operation;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class OperationFixtures extends Fixture implements DependentFixtureInterface
{
    const ROWS = 100;
    const MIN_AMOUNT = -1000;
    const MAX_AMOUNT = 1000;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $start = (new DateTime())->sub(new DateInterval('P1Y'));
        $end = (new DateTime())->add(new DateInterval('P1Y'));

        foreach (range(0, self::ROWS) as $key => $data) {
            $operation = new Operation();
            $operation->setName(md5($key));
            $operation->setAmount(rand(self::MIN_AMOUNT, self::MAX_AMOUNT));
            $operation->setDate((new DateTime())->setTimestamp(rand($start->getTimestamp(), $end->getTimestamp())));
            $operation->setContact($this->getReference(sprintf('%s.%d', ContactFixtures::PREFIX, rand(0, ContactFixtures::ROWS))));
            $operation->setUser($this->getReference('user'));

            $manager->persist($operation);
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
            ContactFixtures::class,
        ];
    }
}
