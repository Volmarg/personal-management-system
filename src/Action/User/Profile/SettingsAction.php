<?php


namespace App\Action\User\Profile;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Entity\User;
use App\Form\System\SystemLockResourcesPasswordType;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class SettingsAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    private UserPasswordEncoderInterface $encoder;

    /**
     * @var User $currentUser
     */
    private $currentUser;

    public function __construct(Application $app, UserPasswordEncoderInterface $encoder, Security $security)
    {

        $this->app         = $app;
        $this->encoder     = $encoder;
        $this->currentUser = $security->getUser();
    }

    /**
     * @Route("/user/profile/settings", name="user_profile_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/user/profile/settings/update", name="user_profile_settings_update")
     * @param Request $request
     * @return Response
     * @throws MappingException
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();

        if (array_key_exists(User::PASSWORD_FIELD, $parameters)) {
            $parameters[User::PASSWORD_FIELD] = $this->encoder->encodePassword($this->currentUser, $parameters[User::PASSWORD_FIELD]);
        }

        $response = $this->app->repositories->update($parameters, $this->currentUser);
        $template = $this->renderTemplate(true)->getContent();

        $ajaxResponse = AjaxResponse::initializeFromResponse($response);
        $ajaxResponse->setTemplate($template);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param false $ajaxRender
     * @return Response
     */
    private function renderTemplate($ajaxRender = false): Response
    {
        $avatar   = $this->currentUser->getAvatar();
        $nickname = $this->currentUser->getNickname();

        $avatarForm       = $this->app->forms->userAvatarForm(['avatar' => $avatar]);
        $passwordForm     = $this->app->forms->userPasswordForm();
        $nicknameForm     = $this->app->forms->userNicknameForm(['nickname' => $nickname]);
        $lockPasswordForm = $this->app->forms->systemLockResourcesPasswordForm([
            SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => true
        ]);

        $data = [
            'ajax_render'        => $ajaxRender,
            'avatar_form'        => $avatarForm->createView(),
            'password_form'      => $passwordForm->createView(),
            'nickname_form'      => $nicknameForm->createView(),
            'lock_password_form' => $lockPasswordForm->createView(),
            'page_title'         => $this->getSettingsPageTitle(),
        ];

        return $this->render('page-elements/user/settings.html.twig', $data);
    }

    /**
     * Will return page title
     *
     * @return string
     */
    private function getSettingsPageTitle(): string
    {
        return $this->app->translator->translate('user.title');
    }

}