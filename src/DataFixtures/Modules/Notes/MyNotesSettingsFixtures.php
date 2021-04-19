<?php

namespace App\DataFixtures\Modules\Notes;

use App\DataFixtures\Providers\Modules\NotesCategories;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyNotesSettingsFixtures extends Fixture implements OrderedFixtureInterface
{
    const FONTAWESOME_ICONS_LIST_JSON_FILE_NAME = 'iconpicker-1.0.0.json';

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        foreach (NotesCategories::ALL_CATEGORIES as $category) {

            $name       = $category[NotesCategories::KEY_NAME];
            $icon       = $category[NotesCategories::KEY_ICON];
            $parentName = $category[NotesCategories::KEY_PARENT_NAME];
            $parentId   = NULL;
            $color      = $this->faker->hexColor;

            if( !empty($parentName) ){
                $notesCategories = $manager->getRepository(MyNotesCategories::class)->findBy(['name' => $parentName]);
                $notesCategory   = reset($notesCategories);
                $parentId        = $notesCategory->getId();
            }

            $notesCategory = new MyNotesCategories();
            $notesCategory->setName($name);
            $notesCategory->setColor($color);

            $notesCategory->setIcon($icon);
            $notesCategory->setParentId($parentId);

            $manager->persist($notesCategory);
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
