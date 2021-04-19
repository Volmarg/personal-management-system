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
use Doctrine\Persistence\ObjectManager;
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
        foreach(Issues::ALL_ISSUES as $issueData){
            $id          = $issueData[Issues::KEY_ID];
            $name        = $issueData[Issues::KEY_NAME];
            $information = $issueData[Issues::KEY_INFORMATION];

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
        foreach(Issues::ALL_ISSUES_CONTACTS as $issueContactData){
            $issueId     = $issueContactData[Issues::KEY_ISSUE_ID];
            $information = $issueContactData[Issues::KEY_INFORMATION];
            $date        = $issueContactData[Issues::KEY_DATE];
            $icon        = $issueContactData[Issues::KEY_ICON];

            $issue = $this->app->repositories->myIssueRepository->find($issueId);

            $issueContact = new MyIssueContact();
            $issueContact->setIssue($issue);
            $issueContact->setInformation($information);
            $issueContact->setIcon($icon);
            $issueContact->setDate(new DateTime($date));

            $manager->persist($issueContact);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addAllIssuesProgress(ObjectManager $manager)
    {
        foreach(Issues::ALL_ISSUES_PROGRESS as $issueProgressData){
            $issueId     = $issueProgressData[Issues::KEY_ISSUE_ID];
            $information = $issueProgressData[Issues::KEY_INFORMATION];
            $date        = $issueProgressData[Issues::KEY_DATE];

            $issue = $this->app->repositories->myIssueRepository->find($issueId);

            $issueProgress = new MyIssueProgress();
            $issueProgress->setIssue($issue);
            $issueProgress->setInformation($information);
            $issueProgress->setDate(new DateTime($date));

            $manager->persist($issueProgress);
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
