<?php

namespace App\Services\Files;

use App\Entity\FilesTags;
use App\Repository\FilesTagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class handles files tagging logic which is:
 * saving / removing/ adding/ updating tags
 * also it will handle the tag entity update upon moving the file to other directory via GUI
 * moving files outside of gui will not be supported here
 * Class FileTagger
 * @package App\Services
 */
class FileTagger {

    const KEY_TAGS = 'tags';

    /**
     * @var string
     */
    private $fullFilePath;

    /**
     * @var array
     */
    private $tags = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly FilesTagsRepository $filesTagsRepository,
    ) {
    }

    /**
     * Set the vars to handle tagging for current file
     * All the tags from input must be passed in as the difference between what's in DB will handle the corresponding action
     * @param array $tags - empty is ok, this means we remove all tags
     * @param string $fullFilePath
     * @throws \Exception
     */
    public function prepare(array $tags, string $fullFilePath) {
        $this->tags         = $tags;
        $this->fullFilePath = $fullFilePath;
    }

    /**
     * This method will get the fileTags entity for full file path,
     * By default the full file path passed as param will be used but if param is passed then it will be used in search
     * @param string|null $fileFullPath
     * @return FilesTags
     * @throws \Exception
     */
    private function getEntity(? string $fileFullPath = null): ?FilesTags {

        $fileFullPath = ( is_null($fileFullPath) ? $this->fullFilePath : $fileFullPath );


        $allFilesWithTags = $this->filesTagsRepository->findBy([
            'fullFilePath' => $fileFullPath
        ]);

        $countedFilesWithTags = count($allFilesWithTags);
        if( $countedFilesWithTags > 1 ){
            $message = $this->translator->trans('exceptions.tagger.moreThanOneFileTagsRecordsFoundForPath') . $fileFullPath;
            throw new \Exception($message);
        }

        if( empty($allFilesWithTags) ){
            return null;
        } else {
            $fileWithTags = reset($allFilesWithTags);
        }

        return $fileWithTags;
    }

    /**
     * This function handles adding/removing tags
     * @throws \Exception
     */
    public function updateTags(){

        if( !$this->isPrepared() ){
            $message = $this->translator->trans('exceptions.tagger.allTagsHaveBeenRemoved');
            throw new \Exception($message);
        }

        try {

            $fileWithTags = $this->getEntity();

            # no tags exist for that file, add them, or do nothing
            if( empty($fileWithTags) && !empty($this->tags) ){
                $tagsJson = $this->arrayTagsToJson($this->tags);

                $fileTags = new FilesTags();
                $fileTags->setFullFilePath($this->fullFilePath);
                $fileTags->setTags($tagsJson);

                $this->em->persist($fileTags);
                $this->em->flush();

                $message = $this->translator->trans('responses.tagger.tagsHaveBeenCreated');
                return new Response($message);
            }

            # no tags exist and not adding any
            if ( empty($fileWithTags) && empty($this->tags) ){
                $message = $this->translator->trans('responses.tagger.noTagsToAdd');
                return new Response($message);
            }

            # tags exist but we just removed them all
            if(
                    // either there are not tags at all
                (
                        !empty($fileWithTags)
                    &&   empty($this->tags)
                )
                ||  // or there is just one tag but it's empty
                (
                        count($this->tags) === 1
                    &&  array_key_exists(0, $this->tags)
                    &&  empty( reset($this->tags) )
                )
                ){
                $this->em->remove($fileWithTags);
                $this->em->flush();

                $message = $this->translator->trans('responses.tagger.allTagsHaveBeenRemoved');
                return new Response($message);
            }

            $currentTagsJson  = $fileWithTags->getTags();
            $currentTagsArray = $this->jsonTagsToArray($currentTagsJson);

            $newTags    = array_diff($this->tags, $currentTagsArray);
            $commonTags = array_intersect($this->tags, $currentTagsArray);

            $areTagsRemoved = ( count($currentTagsArray) !== count($commonTags) );

            if ( empty($newTags) && !$areTagsRemoved ) {
                $message = $this->translator->trans('responses.tagger.noTagsToAdd');
                return new Response($message);
            }

            $tagsArray = array_merge($newTags, $commonTags);
            $tagsJson  = $this->arrayTagsToJson($tagsArray);

            $fileWithTags->setTags($tagsJson);

            $this->em->persist($fileWithTags);
            $this->em->flush();

            $message = $this->translator->trans('responses.tagger.tagsUpdated');
            return new Response($message);

        } catch (\Exception $e) {
            $message = $this->translator->trans('exceptions.tagger.thereWasAnError');
            return new Response($message);
        }

    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function removeTags(){

        $fileWithTags = $this->getEntity();
        if( empty($fileWithTags) ){
            $message = $this->translator->trans('responses.tagger.noTagsToRemove');
            return new Response($message);
        }else{
            $this->em->remove($fileWithTags);
            $this->em->flush();

            $message = $this->translator->trans('responses.tagger.allTagsHaveBeenRemoved');
            return new Response($message);
        }

    }

    private function jsonTagsToArray(string $json): array {
        $tags = \GuzzleHttp\json_decode($json, true);
        return $tags;
    }

    private function arrayTagsToJson(array $tags): string{
        $json = \GuzzleHttp\json_encode($tags);
        return $json;
    }

    /**
     * Check if vars have been set
     */
    private function isPrepared(){

        if(
                !isset($this->fullFilePath)
            ||  !isset($this->tags)
        ){
            return false;
        }

        return true;
    }

    /**
     * @param string $oldFilePath
     * @param string $newFilePath
     * @throws \Exception
     */
    public function updateFilePath(string $oldFilePath, string $newFilePath) {

        $fileTags = $this->getEntity($oldFilePath);
        if( !$fileTags ){
            return;
        }

        $fileTags->setFullFilePath($newFilePath);

        $this->em->persist($fileTags);
        $this->em->flush();
    }

    /**
     * @param string $oldFolderPath
     * @param string $newFolderPath
     * @throws \Exception
     */
    public function updateFilePathByFolderPathChange(string $oldFolderPath, string $newFolderPath) {
        $this->filesTagsRepository->updateFilePathByFolderPathChange($oldFolderPath, $newFolderPath);
    }

    /**
     * This will copy the current set of tag and create new set with new path
     * @param string $currentFilePath
     * @param string $copyFilePath
     * @throws \Exception
     */
    public function copyTagsFromPathToNewPath(string $currentFilePath, string $copyFilePath): void {

        $tagsArr  = [];
        $fileTags = $this->getEntity($currentFilePath);

        if( !empty($fileTags) ){
            $tagsJson = $fileTags->getTags();
            $tagsArr  = \GuzzleHttp\json_decode($tagsJson);
        }

        $this->prepare($tagsArr, $copyFilePath);
        $this->updateTags();

    }

}