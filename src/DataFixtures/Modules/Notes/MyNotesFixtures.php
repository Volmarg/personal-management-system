<?php

namespace App\DataFixtures\Modules\Notes;


use App\DataFixtures\Providers\Modules\Notes;
use App\DataFixtures\Providers\Modules\NotesCategories;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
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

            $category_name  = $category[Notes::KEY_CATEGORY_NAME];
            $body           = $category[Notes::KEY_BODY];
            $name           = $category[Notes::KEY_NAME];

            $notes_categories = $manager->getRepository(MyNotesCategories::class)->findBy(['name' => $category_name]);
            $note_category    = reset($notes_categories);

            $my_note = new MyNotes();
            $my_note->setCategory($note_category);
            $my_note->setBody($body);
            $my_note->setTitle($name);

            $manager->persist($my_note);

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
