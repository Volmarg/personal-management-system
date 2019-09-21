<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use \App\DataFixtures\Providers\FilesTags as FilesTagsProvider;
use \App\Entity\FilesTags                 as FilesTagsEntity;

class FilesTags extends Fixture
{

    public function load(ObjectManager $manager) {

        foreach ( FilesTagsProvider::DATA_SETS as $data_set ){

            $files_tags = new FilesTagsEntity();
            $files_tags->setFullFilePath($data_set[FilesTagsProvider::KEY_FILEPATH]);
            $files_tags->setTags($data_set[FilesTagsProvider::KEY_JSON_TAGS]);

            $manager->persist($files_tags);
        }

        $manager->flush();
    }
}
