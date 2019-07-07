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

class SettingsController extends AbstractController {

    /**
     * This is personal management system so I do not even expect more users for it.
     */
    const USER_ID = 1;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var User $userEntity
     */
    private $userEntity;

    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    private $encoder;

    public function __construct(Application $app, UserPasswordEncoderInterface $encoder) {

        $this->userEntity   = $app->repositories->userRepository->find(static::USER_ID);
        $this->app          = $app;
        $this->encoder      = $encoder;
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
            $parameters[User::PASSWORD_FIELD] = $this->encoder->encodePassword($this->userEntity, $parameters[User::PASSWORD_FIELD]);
        }

        $response   = $this->app->repositories->update($parameters, $this->userEntity);

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
        return $this->createForm(UserAvatarType::class, null, ['avatar' => $this->userEntity->getAvatar()]);
    }

    private function getPasswordForm() {
        return $this->createForm(UserPasswordType::class);
    }

    private function getNicknameForm() {
        return $this->createForm(UserNicknameType::class, null, ['nickname' => $this->userEntity->getNickname()]);
    }

}
