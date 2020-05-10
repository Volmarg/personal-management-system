<?php


namespace App\Action\User\Profile;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Entity\User;
use App\Form\System\SystemLockResourcesPasswordType;
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
    private $app;

    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    private $encoder;

    /**
     * @var User $current_user
     */
    private $current_user;

    public function __construct(Application $app, UserPasswordEncoderInterface $encoder, Security $security) {

        $this->app          = $app;
        $this->encoder      = $encoder;
        $this->current_user = $security->getUser();
    }

    /**
     * @Route("/user/profile/settings", name="user_profile_settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/user/profile/settings/update", name="user_profile_settings_update")
     * @param Request $request
     * @return Response
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();

        if (array_key_exists(User::PASSWORD_FIELD, $parameters)) {
            $parameters[User::PASSWORD_FIELD] = $this->encoder->encodePassword($this->current_user, $parameters[User::PASSWORD_FIELD]);
        }

        $response   = $this->app->repositories->update($parameters, $this->current_user);
        $message    = $response->getContent();
        $template   = $this->renderTemplate(true)->getContent();

        $code = $response->getStatusCode();
        return AjaxResponse::buildResponseForAjaxCall($code, $message, $template);
    }

    private function renderTemplate($ajax_render = false) {
        $avatar   = $this->current_user->getAvatar();
        $nickname = $this->current_user->getNickname();

        $avatar_form        = $this->app->forms->userAvatarForm(['avatar' => $avatar]);
        $password_form      = $this->app->forms->userPasswordForm();
        $nickname_form      = $this->app->forms->userNicknameForm(['nickname' => $nickname]);
        $lock_password_form = $this->app->forms->systemLockResourcesPasswordForm([
            SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => true
        ]);

        $data = [
            'ajax_render'        => $ajax_render,
            'avatar_form'        => $avatar_form->createView(),
            'password_form'      => $password_form->createView(),
            'nickname_form'      => $nickname_form->createView(),
            'lock_password_form' => $lock_password_form->createView(),
        ];

        return $this->render('page-elements/user/settings.html.twig', $data);
    }

}