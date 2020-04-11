<?php


namespace App\Action\Core;


use App\Controller\Core\Application;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepositoriesAction extends AbstractController {


    const KEY_PARAMETERS        = 'parameters';
    const KEY_ENTITY_ID         = 'entity_id';
    const KEY_REPOSITORY_NAME   = 'repository_name';


    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct(Application $app, Translator $translator) {
        $this->app        = $app;
        $this->translator = $translator;
    }

    /**
     * @Route("/api/repository/remove/entity/{repository_name}/{id}", name="api_repository_remove_entity")
     * @param string $repository_name
     * @param $id
     * @param array $findByParams
     * @param Request|null $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function deleteById(string $repository_name, $id, array $findByParams = [], ?Request $request = null ): Response
    {
        $response = $this->app->repositories->deleteById($repository_name, $id, $findByParams, $request);
        return $response;
    }


    /**
     * @Route("/api/repository/update/entity", name="api_repository_update_entity")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateByRequest(Request $request){

        if( !$request->request->has(self::KEY_PARAMETERS) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_PARAMETERS;
            return new JsonResponse($message, 500);
        }

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_ENTITY_ID;
            return new JsonResponse($message, 500);
        }

        if( !$request->request->has(self::KEY_REPOSITORY_NAME) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_REPOSITORY_NAME;
            return new JsonResponse($message, 500);
        }

        $parameters      = $request->request->get(self::KEY_PARAMETERS);
        $id              = $request->request->get(self::KEY_ENTITY_ID);
        $repository_name = $request->request->get(self::KEY_REPOSITORY_NAME);

        $id         = $this->app->repositories->trimAndCheckId($id);
        $repository = $this->{lcfirst($repository_name)};
        $entity     = $repository->find($id);

        $response = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

}