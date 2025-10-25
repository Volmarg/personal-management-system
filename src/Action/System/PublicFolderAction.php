<?php

namespace App\Action\System;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Relies on the token added to the file that is getting to be downloaded because the standard jwt authentication has
 * been disabled on the download links. Had to be because it has to work natively in browser and not with all the ajax
 * requests which are all over the place as this would at some point need handling of reading binary file on front
 * and saving it etc.
 */
class PublicFolderAction extends AbstractController
{

    /**
     * Handles downloading file in the secure manners, meaning that downloaded data is controlled with who got actually
     * access to it, instead of just serving everything from public folder.
     *
     * @throws Exception
     */
    #[Route("/public/get-file/{path}", name: "file.get", requirements: ["path" => ".+"], methods: [Request::METHOD_OPTIONS, Request::METHOD_GET])]
    public function getFromFolder(string $path, Request $request): Response
    {
        if ($request->query->has('force-full-size')) {
            return $this->buildResponse($path);
        }

        // todo: configure apache settings so that no file from /public can be called besides index.php / favicon etc. (protect files from download)
        // todo: check if miniature exist then return it, else get full size
        return $this->buildResponse($path);
    }

    /**
     * @param string $fileName
     * @param string $fullFilePathWithName
     *
     * @return Response
     * @throws Exception
     */
    private function buildResponse(string $filePath): Response
    {
        $fileContent = file_get_contents($filePath);
        if (is_bool($fileContent)) {
            throw new Exception("Could not open the file: {$filePath}");
        }

        $response    = new Response($fileContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            pathinfo($filePath, PATHINFO_FILENAME) . "." . pathinfo($filePath, PATHINFO_EXTENSION),
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}