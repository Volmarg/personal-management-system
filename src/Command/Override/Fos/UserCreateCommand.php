<?php

namespace App\Command\Override\Fos;

use App\Controller\System\SecurityController;
use App\Controller\Core\Application;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\UserBundle\Command\CreateUserCommand;
use FOS\UserBundle\Util\UserManipulator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Overriding the fos command logic due to required support for lockField and impossibility to extend command logic
 * Class UserCreateCommand
 * @package App\Command\Override\Fos
 */
class UserCreateCommand extends CreateUserCommand
{

    const KEY_USERNAME      = "username";
    const KEY_EMAIL         = "email";
    const KEY_PASSWORD      = "password";
    const KEY_SUPER_ADMIN   = "super-admin";
    const KEY_INACTIVE      = "inactive";
    const KEY_LOCK_PASSWORD = "lock-password";

    /**
     * @var UserManipulator $userManipulator
     */
    private $userManipulator;

    /**
     * @var SecurityController $securityController
     */
    private $securityController;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(UserManipulator $userManipulator, Application $app, SecurityController $securityController) {
        parent::__construct($userManipulator);
        $this->securityController = $securityController;
        $this->userManipulator    = $userManipulator;
        $this->app                = $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:user:create')
            ->setDescription('Create a user.')
            ->setDefinition(array(
                new InputArgument(self::KEY_USERNAME, InputArgument::REQUIRED, 'The username'),
                new InputArgument(self::KEY_EMAIL, InputArgument::REQUIRED, 'The email'),
                new InputArgument(self::KEY_PASSWORD, InputArgument::REQUIRED, 'The password'),
                new InputArgument(self::KEY_LOCK_PASSWORD, InputArgument::REQUIRED, 'The lock password'),
                new InputOption(self::KEY_SUPER_ADMIN, null, InputOption::VALUE_NONE, 'Set the user as super admin'),
                new InputOption(self::KEY_INACTIVE, null, InputOption::VALUE_NONE, 'Set the user as inactive'),
            ))
            ->setHelp(<<<'EOT'
The <info>fos:user:create</info> command creates a user:

  <info>php %command.full_name% matthieu</info>

This interactive shell will ask you for an email and then a password.

You can alternatively specify the email and password as the second and third arguments:

  <info>php %command.full_name% matthieu matthieu@example.com mypassword</info>

You can create a super admin via the super-admin flag:

  <info>php %command.full_name% admin --super-admin</info>

You can create an inactive user (will not be able to log in):

  <info>php %command.full_name% thibault --inactive</info>

EOT
            );
    }

    /**
     * {@inheritdoc}
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username      = $input->getArgument(self::KEY_USERNAME);
        $email         = $input->getArgument(self::KEY_EMAIL);
        $password      = $input->getArgument(self::KEY_PASSWORD);
        $lock_password = $input->getArgument(self::KEY_LOCK_PASSWORD);
        $inactive      = $input->getOption(self::KEY_INACTIVE);

        $app_user  = new User();
        $app_user->setUsername($username);
        $app_user->setUsernameCanonical($username);
        $app_user->setEmail($email);
        $app_user->setEmailCanonical($email);
        $app_user->setEnabled(!$inactive);
        $app_user->setRoles([User::ROLE_SUPER_ADMIN]);
        $app_user->setNickname($username);

        $security_dto    = $this->securityController->hashPassword($password);
        $hashed_password = $security_dto->getHashedPassword();

        $security_dto         = $this->securityController->hashPassword($lock_password);
        $hashed_lock_password = $security_dto->getHashedPassword();

        $app_user->setPassword($hashed_password);
        $app_user->setLockPassword($hashed_lock_password);

        $this->app->repositories->userRepository->saveUser($app_user);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument(self::KEY_USERNAME)) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $questions[self::KEY_USERNAME] = $question;
        }

        if (!$input->getArgument(self::KEY_EMAIL)) {
            $question = new Question('Please choose an email:');
            $question->setValidator(function ($email) {
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }

                return $email;
            });
            $questions[self::KEY_EMAIL] = $question;
        }

        if (!$input->getArgument(self::KEY_PASSWORD)) {
            $question = new Question('Please choose a password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions[self::KEY_PASSWORD] = $question;
        }

        if (!$input->getArgument(self::KEY_LOCK_PASSWORD)) {
            $question = new Question('Please choose a lock password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Lock password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions[self::KEY_LOCK_PASSWORD] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

}
