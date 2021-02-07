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
     * @var int $count_of_payments_to_add
     */
    private $count_of_payments_to_add = 0;

    /**
     * @var int $count_of_already_existing_payments
     */
    private $count_of_already_existing_payments = 0;

    /**
     * @var int $count_of_filtered_payments
     */
    private $count_of_filtered_payments = 0;

    /**
     * @var int $count_of_added_payments
     */
    private $count_of_added_payments = 0;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var string $curr_year_month
     */
    private $curr_year_month;

    public function __construct(Application $app, string $name = null) {
        parent::__construct($name);
        $this->app             = $app;
        $this->curr_year_month = (new DateTime())->format('Y-m');
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
        $io->success("Started setting recurring payments for month and year: " . $this->curr_year_month);
        {
            try{
                $recurring_payments_to_set = $this->getRecurringPaymentsAndFilterExistingRecords();

                if( !$this->count_of_filtered_payments ){
                    $io->note("No entries were found to process. Stopping here");
                    return Command::SUCCESS;
                }

                $this->addRecurringPaymentsToDatabase($recurring_payments_to_set);
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
        $recurring_payments             = $this->app->repositories->myRecurringPaymentMonthlyRepository->findBy(['deleted' => 0]);
        $this->count_of_payments_to_add = count($recurring_payments);

        foreach($recurring_payments as $index => $recurring_payment){

            $used_day_of_month          = $this->getDayOfMonthForInsertion($recurring_payment);
            $hash                       = $this->calculateHashForAttemptedInsertion($recurring_payment, $used_day_of_month);
            $recurring_payment_for_hash = $this->app->repositories->myPaymentsMonthlyRepository->findByDateAndDescriptionHash($hash, $used_day_of_month);

            $this->count_of_already_existing_payments += count($recurring_payment_for_hash);

            if( !empty($recurring_payment_for_hash) ){
                unset($recurring_payments[$index]);
            }

        }
        $this->count_of_filtered_payments = count($recurring_payments);

        return $recurring_payments;
    }

    /**
     * @param MyRecurringPaymentMonthly[] $recurring_payments_to_set
     * @throws Exception
     */
    private function addRecurringPaymentsToDatabase(array $recurring_payments_to_set):void {

        foreach($recurring_payments_to_set as $recurring_payment){
            $used_day_of_month              = $this->getDayOfMonthForInsertion($recurring_payment);
            $date_time_string_for_insertion = (new DateTime())->format("Y-m-") . $used_day_of_month;
            $date_time_for_insertion        = DateTime::createFromFormat("Y-m-d", $date_time_string_for_insertion);

            $payment = new MyPaymentsMonthly();
            $payment->setDescription($recurring_payment->getDescription());
            $payment->setDate($date_time_for_insertion);
            $payment->setMoney($recurring_payment->getMoney());
            $payment->setType($recurring_payment->getType());

            $this->app->em->persist($payment);
            $this->app->em->flush();
            $this->count_of_added_payments++;
        }

    }

    /**
     * This function will display information about adding the payments
     * @param SymfonyStyle $io
     */
    private function report(SymfonyStyle $io){
        $io->note("Number of recurring payments found to add: "         . $this->count_of_payments_to_add);
        $io->note("Number of already existing recurring payments: "     . $this->count_of_already_existing_payments);
        $io->note("Number of recurring payments that will be added: "   . $this->count_of_filtered_payments);
        $io->note("Number of added recurring payments: "                . $this->count_of_added_payments);
    }

    /**
     * Will calculate hash for given entity - this value is used to determine if given entry
     * exists already in database or not
     *
     * @param MyRecurringPaymentMonthly $recurring_payment
     * @param string $used_day_of_month
     * @return string
     */
    private function calculateHashForAttemptedInsertion(MyRecurringPaymentMonthly $recurring_payment, string $used_day_of_month): string
    {
        $date_time_string_for_insertion = (new DateTime())->format("Y-m-") . $used_day_of_month;
        $string                         = $date_time_string_for_insertion . $recurring_payment->getDescription();

        $hash = md5($string);
        return $hash;
    }

    /**
     * This method will get the real day of month used for the insertion, this is required
     * due to the fact that there might be case where user inserted day of month `31` in the form
     * but given month might have on 28 days, so the last day of given month will be used instead
     *
     * @param MyRecurringPaymentMonthly $recurring_payment
     * @return string
     */
    private function getDayOfMonthForInsertion(MyRecurringPaymentMonthly $recurring_payment): string
    {
        $used_day_of_month     = $recurring_payment->getDayOfMonth();
        $days_in_current_month = (new DateTime())->format("t");

        if($days_in_current_month <= $used_day_of_month){
            $used_day_of_month = $days_in_current_month;
        }

        return $used_day_of_month;
    }

}
