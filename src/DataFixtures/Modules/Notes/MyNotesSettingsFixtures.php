<?php

namespace App\DataFixtures\Modules\Notes;

use App\DataFixtures\Providers\Modules\NotesCategories;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyNotesSettingsFixtures extends Fixture implements OrderedFixtureInterface
{
    const FONTAWESOME_ICONS_LIST_JSON_FILE_NAME = 'iconpicker-1.0.0.json';

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker                     = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        foreach (NotesCategories::ALL_CATEGORIES as $category) {

            $name        = $category[NotesCategories::KEY_NAME];
            $icon        = $category[NotesCategories::KEY_ICON];
            $parent_name = $category[NotesCategories::KEY_PARENT_NAME];
            $parent_id   = NULL;
            $color       = $this->faker->hexColor;

            if( !empty($parent_name) ){
                $notes_categories = $manager->getRepository(MyNotesCategories::class)->findBy(['name' => $parent_name]);
                $notes_category   = reset($notes_categories);
                $parent_id        = $notes_category->getId();
            }

            $notes_category = new MyNotesCategories();
            $notes_category->setName($name);
            $notes_category->setColor($color);

            $notes_category->setIcon($icon);
            $notes_category->setParentId($parent_id);

            $manager->persist($notes_category);
            $manager->flush();

        }

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 7;
    }
}
