<?php

namespace App\DataFixtures\Modules\Notes;


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
        for($x = 0; $x <= 25; $x++) {

            $notes_categories = $manager->getRepository(MyNotesCategories::class)->findAll();
            $index            = array_rand($notes_categories);
            $note_category    = $notes_categories[$index];

            $title            = $this->faker->word;
            $body             = $this->faker->text(700);

            $my_note          = new MyNotes();
            $my_note->setCategory($note_category);
            $my_note->setBody($body);
            $my_note->setTitle($title);

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
