<?php

namespace App\DataFixtures\Modules\Notes;


use App\DataFixtures\Providers\Modules\Notes;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyNotesFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    public function load(ObjectManager $manager)
    {

        foreach (Notes::ALL_CATEGORIES as $category) {

            $categoryName = $category[Notes::KEY_CATEGORY_NAME];
            $body         = $category[Notes::KEY_BODY];
            $name         = $category[Notes::KEY_NAME];

            $notesCategories = $manager->getRepository(MyNotesCategories::class)->findBy(['name' => $categoryName]);
            $noteCategory    = reset($notesCategories);

            $myNote = new MyNotes();
            $myNote->setCategory($noteCategory);
            $myNote->setBody($body);
            $myNote->setTitle($name);

            $manager->persist($myNote);

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 8;
    }
}
