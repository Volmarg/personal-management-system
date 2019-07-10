<?php

namespace App\Controller\User\Profile;

use App\Controller\Utils\Application;
use App\Entity\User;
use App\Form\User\UserAvatarType;
use App\Form\User\UserNicknameType;
use App\Form\User\UserPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class SettingsController extends AbstractController {

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        return $this->renderTemplate(true);
    }

    /**
     * @Route("/user/profile/settings/update", name="user_profile_settings_update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();

        if (array_key_exists(User::PASSWORD_FIELD, $parameters)) {
            $parameters[User::PASSWORD_FIELD] = $this->encoder->encodePassword($this->current_user, $parameters[User::PASSWORD_FIELD]);
        }

        $response   = $this->app->repositories->update($parameters, $this->current_user);

        if($response->getStatusCode() === 200){
            return $this->renderTemplate(true);
        }

        return $response;

    }

    protected function renderTemplate($ajax_render = false) {
        $avatarForm   = $this->getAvatarForm();
        $passwordForm = $this->getPasswordForm();
        $nicknameForm = $this->getNicknameForm();

        $data = [
            'ajax_render'       => $ajax_render,
            'avatar_form'       => $avatarForm->createView(),
            'password_form'     => $passwordForm->createView(),
            'nickname_form'     => $nicknameForm->createView()
        ];

        return $this->render('page-elements/user/settings.html.twig', $data);
    }

    private function getAvatarForm() {
        $avatar = $this->current_user->getAvatar();
        return $this->createForm(UserAvatarType::class, null, ['avatar' => $avatar]);
    }

    private function getPasswordForm() {
        return $this->createForm(UserPasswordType::class);
    }

    private function getNicknameForm() {
        $nickname = $this->current_user->getNickname();
        return $this->createForm(UserNicknameType::class, null, ['nickname' => $nickname]);
    }

}
