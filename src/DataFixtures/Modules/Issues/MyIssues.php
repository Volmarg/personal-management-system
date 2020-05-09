<?php

namespace App\DataFixtures\Modules\Issues;

use App\Controller\Core\Application;
use App\DataFixtures\Providers\Modules\Issues;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
use Faker\Factory;

class MyIssues extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->faker = Factory::create('en');
        $this->app   = $app;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->resetIssuesIndex();
        $this->addAllIssues($manager);
        $this->addAllIssuesContacts($manager);
        $this->addAllIssuesProgress($manager);
    }

    private function addAllIssues(ObjectManager $manager){
        foreach(Issues::ALL_ISSUES as $issue_data){
            $id          = $issue_data[Issues::KEY_ID];
            $name        = $issue_data[Issues::KEY_NAME];
            $information = $issue_data[Issues::KEY_INFORMATION];

            $issue = new MyIssue();
            $issue->setId($id);
            $issue->setName($name);
            $issue->setInformation($information);

            $manager->persist($issue);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addAllIssuesContacts(ObjectManager $manager)
    {
        foreach(Issues::ALL_ISSUES_CONTACTS as $issue_contact_data){
            $issue_id    = $issue_contact_data[Issues::KEY_ISSUE_ID];
            $information = $issue_contact_data[Issues::KEY_INFORMATION];
            $date        = $issue_contact_data[Issues::KEY_DATE];
            $icon        = $issue_contact_data[Issues::KEY_ICON];

            $issue = $this->app->repositories->myIssueRepository->find($issue_id);

            $issue_contact = new MyIssueContact();
            $issue_contact->setIssue($issue);
            $issue_contact->setInformation($information);
            $issue_contact->setIcon($icon);
            $issue_contact->setDate(new DateTime($date));

            $manager->persist($issue_contact);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addAllIssuesProgress(ObjectManager $manager)
    {
        foreach(Issues::ALL_ISSUES_PROGRESS as $issue_progress_data){
            $issue_id    = $issue_progress_data[Issues::KEY_ISSUE_ID];
            $information = $issue_progress_data[Issues::KEY_INFORMATION];
            $date        = $issue_progress_data[Issues::KEY_DATE];

            $issue = $this->app->repositories->myIssueRepository->find($issue_id);

            $issue_progress = new MyIssueProgress();
            $issue_progress->setIssue($issue);
            $issue_progress->setInformation($information);
            $issue_progress->setDate(new DateTime($date));

            $manager->persist($issue_progress);
        }
        $manager->flush();
    }

    /**
     * @throws DBALException
     */
    private function resetIssuesIndex()
    {
        $connection = $this->app->em->getConnection();

        $sql = "
            ALTER TABLE my_issue AUTO_INCREMENT = 1
        ";

        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 19;
    }
}
