<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronAddRecurringPaymentsCommand extends Command
{
    protected static $defaultName = 'cron:set-recurring-payments';

    /**
     * @var int $countOfPaymentsToAdd
     */
    private $countOfPaymentsToAdd = 0;

    /**
     * @var int $countOfAlreadyExistingPayments
     */
    private $countOfAlreadyExistingPayments = 0;

    /**
     * @var int $countOfFilteredPayments
     */
    private $countOfFilteredPayments = 0;

    /**
     * @var int $countOfAddedPayments
     */
    private $countOfAddedPayments = 0;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var string $currYearMonth
     */
    private $currYearMonth;

    public function __construct(Application $app, string $name = null) {
        parent::__construct($name);
        $this->app           = $app;
        $this->currYearMonth = (new DateTime())->format('Y-m');
    }

    protected function configure()
    {
        $this
            ->setDescription("This command will set recurring payments for current month and year");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $io->success("Started setting recurring payments for month and year: " . $this->currYearMonth);
        {
            try{
                $recurringPaymentsToSet = $this->getRecurringPaymentsAndFilterExistingRecords();

                if( !$this->countOfFilteredPayments ){
                    $io->note("No entries were found to process. Stopping here");
                    return Command::SUCCESS;
                }

                $this->addRecurringPaymentsToDatabase($recurringPaymentsToSet);
                $this->report($io);

            }catch(Exception $e){
                $this->app->logger->critical("There was an error while trying to add recurring payments.");
                $this->app->logger->critical($e->getMessage());
                return Command::FAILURE;
            }

        }
        $io->success("Finished setting recurring payments.");

        return Command::SUCCESS;
    }

    /**
     * This function will check which records already exist for given month
     * Returns only the records that are not yet in the database
     * @return array
     */
    private function getRecurringPaymentsAndFilterExistingRecords():array {
        $recurringPayments          = $this->app->repositories->myRecurringPaymentMonthlyRepository->findBy(['deleted' => 0]);
        $this->countOfPaymentsToAdd = count($recurringPayments);

        foreach($recurringPayments as $index => $recurringPayment){

            $usedDayOfMonth          = $this->getDayOfMonthForInsertion($recurringPayment);
            $hash                    = $this->calculateHashForAttemptedInsertion($recurringPayment, $usedDayOfMonth);
            $recurringPaymentForHash = $this->app->repositories->myPaymentsMonthlyRepository->findByDateAndDescriptionHash($hash, $usedDayOfMonth);

            $this->countOfAlreadyExistingPayments += count($recurringPaymentForHash);

            if( !empty($recurringPaymentForHash) ){
                unset($recurringPayments[$index]);
            }

        }
        $this->countOfFilteredPayments = count($recurringPayments);

        return $recurringPayments;
    }

    /**
     * @param MyRecurringPaymentMonthly[] $recurringPaymentsToSet
     * @throws Exception
     */
    private function addRecurringPaymentsToDatabase(array $recurringPaymentsToSet):void {

        foreach($recurringPaymentsToSet as $recurringPayment){
            $usedDayOfMonth             = $this->getDayOfMonthForInsertion($recurringPayment);
            $dateTimeStringForInsertion = (new DateTime())->format("Y-m-") . $usedDayOfMonth;
            $dateTimeForInsertion       = DateTime::createFromFormat("Y-m-d", $dateTimeStringForInsertion);

            $payment = new MyPaymentsMonthly();
            $payment->setDescription($recurringPayment->getDescription());
            $payment->setDate($dateTimeForInsertion);
            $payment->setMoney($recurringPayment->getMoney());
            $payment->setType($recurringPayment->getType());

            $this->app->em->persist($payment);
            $this->app->em->flush();
            $this->countOfAddedPayments++;
        }

    }

    /**
     * This function will display information about adding the payments
     * @param SymfonyStyle $io
     */
    private function report(SymfonyStyle $io){
        $io->note("Number of recurring payments found to add: "         . $this->countOfPaymentsToAdd);
        $io->note("Number of already existing recurring payments: "     . $this->countOfAlreadyExistingPayments);
        $io->note("Number of recurring payments that will be added: "   . $this->countOfFilteredPayments);
        $io->note("Number of added recurring payments: "                . $this->countOfAddedPayments);
    }

    /**
     * Will calculate hash for given entity - this value is used to determine if given entry
     * exists already in database or not
     *
     * @param MyRecurringPaymentMonthly $recurringPayment
     * @param string $usedDayOfMonth
     * @return string
     */
    private function calculateHashForAttemptedInsertion(MyRecurringPaymentMonthly $recurringPayment, string $usedDayOfMonth): string
    {
        $dateTimeStringForInsertion = (new DateTime())->format("Y-m-") . $usedDayOfMonth;
        $string                     = $dateTimeStringForInsertion . $recurringPayment->getDescription();

        $hash = md5($string);
        return $hash;
    }

    /**
     * This method will get the real day of month used for the insertion, this is required
     * due to the fact that there might be case where user inserted day of month `31` in the form
     * but given month might have on 28 days, so the last day of given month will be used instead
     *
     * @param MyRecurringPaymentMonthly $recurringPayment
     * @return string
     */
    private function getDayOfMonthForInsertion(MyRecurringPaymentMonthly $recurringPayment): string
    {
        $usedDayOfMonth     = $recurringPayment->getDayOfMonth();
        $daysInCurrentMonth = (new DateTime())->format("t");

        if($daysInCurrentMonth <= $usedDayOfMonth){
            $usedDayOfMonth = $daysInCurrentMonth;
        }

        return $usedDayOfMonth;
    }

}
