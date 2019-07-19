<?php

namespace App\DataFixtures\Modules\Notes;

use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Finder\Finder;

class MyNotesSettingsFixtures extends Fixture implements OrderedFixtureInterface
{
    const FONTAWESOME_ICONS_LIST_JSON_FILE_NAME = 'iconpicker-1.0.0.json';

    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->faker    = Factory::create('en');
        $this->finder   = new Finder();
    }

    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= 15; $x++) {

            $no_parent            = NULL;
            $parent_ids           = [];

            $notes_categories     = $manager->getRepository(MyNotesCategories::class)->findAll();

            /**
             * @var MyNotesCategories $notes_category
             */
            foreach($notes_categories as $notes_category){
                $parent_ids[] = $notes_category->getId();
            }

            $parent_ids[]         = $no_parent;
            $index                = array_rand($parent_ids);
            $parent_id            = $parent_ids[$index];

            $name                 = $this->faker->word;
            $color                = $this->faker->hexColor;

            $notes_category = new MyNotesCategories();
            $notes_category->setName($name);
            $notes_category->setColor($color);
            $notes_category->setId($x);

            $finder             = $this->finder->name(static::FONTAWESOME_ICONS_LIST_JSON_FILE_NAME)->in('/var/www');
            $file_path          = '';

            foreach($finder as $file){
                $file_path = $file->getRealPath();
            }

            if(file_exists($file_path)){

                $file_content          = file_get_contents($file_path);
                $fontawesome_icons_arr = json_decode($file_content, true);

                if(!array_key_exists('icons', $fontawesome_icons_arr)){
                    throw new \Exception('Json file with fontawesome icons has incorrect structure - missing "icons" key.');
                }

                $icons                 = $fontawesome_icons_arr['icons'];
                $index                 = array_rand($icons);
                $icon                  = $icons[$index];

                $notes_category->setIcon($icon);
            }

            if($x != $parent_id){
                $notes_category->setParentId($parent_id);
            }

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
