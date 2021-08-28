<?php

namespace App\Action\System;

use App\Services\Security\EncryptionService;
use App\Twig\System\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class EncryptionAction extends AbstractController
{

    /**
     * @var EncryptionService $encryptionService
     */
    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/encryption/download-decrypted-file", name="download_decrypted_file", methods={"GET"})
     */
    public function downloadDecryptedFile(Request $request): Response
    {
        // todo: need to update logic of LightGallery to download rename etc / same with other upload based logic
        //  the same about mass actions stuff.
        $filePath = urldecode($request->get(Security::QUERY_PARAM_FILE_PATH));

        $fileName             = basename($filePath);
        $absoluteFilePath     = getcwd() . $filePath;
        $decryptedFileContent = $this->encryptionService->decryptFileContent($absoluteFilePath);

        $response    = new Response($decryptedFileContent);
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

}