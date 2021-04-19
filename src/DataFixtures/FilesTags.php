<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use \App\DataFixtures\Providers\FilesTags as FilesTagsProvider;
use \App\Entity\FilesTags                 as FilesTagsEntity;

class FilesTags extends Fixture
{

    public function load(ObjectManager $manager) {

        foreach ( FilesTagsProvider::DATA_SETS as $dataSet ){

            $filesTags = new FilesTagsEntity();
            $filesTags->setFullFilePath($dataSet[FilesTagsProvider::KEY_FILEPATH]);
            $filesTags->setTags($dataSet[FilesTagsProvider::KEY_JSON_TAGS]);

            $manager->persist($filesTags);
        }

        $manager->flush();
    }
}
