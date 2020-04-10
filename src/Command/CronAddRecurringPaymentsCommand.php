<?php

namespace App\Command;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronAddRecurringPaymentsCommand extends Command
{
    const OPTION_DAY = 'day';

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
        $this->curr_year_month = (new \DateTime())->format('Y-m');
    }

    protected function configure()
    {
        $this
            ->setDescription("This command will set recurring payments for current month and year")
            ->addOption(self::OPTION_DAY, null,InputOption::VALUE_REQUIRED, 'Date which should be used for payment in format DD (only days)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $io->success("Started setting recurring payments for month and year: " . $this->curr_year_month);
        {
            try{
                $option_payment_day             = $input->getOption(self::OPTION_DAY);
                $recurring_payments_to_set      = $this->getRecurringPaymentsAndFilterExistingRecords($io);

                if( 0 !== $this->count_of_filtered_payments ){
                    $this->addRecurringPaymentsToDatabase($recurring_payments_to_set, $option_payment_day, $io);
                }

                $this->report($io);

            }catch(\Exception $e){
                $this->app->logger->critical("There was an error while trying to add recurring payments.");
                $this->app->logger->critical($e->getMessage());
            }

        }
        $io->success("Finished setting recurring payments.");
    }

    /**
     * This function will check which records already exist for given month
     * Returns only the records that are not yet in the database
     * @param SymfonyStyle $io
     * @return array
     */
    private function getRecurringPaymentsAndFilterExistingRecords(SymfonyStyle $io):array {
        $recurring_payments             = $this->app->repositories->myRecurringPaymentMonthlyRepository->findBy(['deleted' => 0]);
        $this->count_of_payments_to_add = count($recurring_payments);

        foreach($recurring_payments as $index => $recurring_payment){

            $hash                       = $recurring_payment->getHash();
            $recurring_payment_for_hash = $this->app->repositories->myPaymentsMonthlyRepository->findByDateAndDescriptionHash($hash);

            $this->count_of_already_existing_payments += count($recurring_payment_for_hash);

            if( !empty($recurring_payment_for_hash) ){
                unset($recurring_payments[$index]);
            }

        }
        $this->count_of_filtered_payments = count($recurring_payments);

        return $recurring_payments;
    }

    /**
     * @param array $recurring_payments_to_set
     * @param string|null $option_payment_day
     * @param SymfonyStyle $io
     * @throws \Exception
     */
    private function addRecurringPaymentsToDatabase(array $recurring_payments_to_set, ?string $option_payment_day, SymfonyStyle $io):void {

        /**
         * @var MyRecurringPaymentMonthly $recurring_payment
         */
        foreach($recurring_payments_to_set as $recurring_payment){

            if( !is_null($option_payment_day) ){
                $date_time = new \DateTime($this->curr_year_month . '-'. $option_payment_day);
                $recurring_payment->setDate($date_time);
            }else{
                $recurring_payment->getDate();
            }

            $payment = new MyPaymentsMonthly();
            $payment->setDescription($recurring_payment->getDescription());
            $payment->setDate($recurring_payment->getDate());
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
}
