<?php

namespace App\DataFixtures\Modules\Schedule;

use App\Controller\Core\Application;
use App\DataFixtures\Providers\FontawesomeIconsProvider;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\MyScheduleReminder;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;

class MyScheduleFixtures extends Fixture implements OrderedFixtureInterface
{
    const COUNT_OF_ENTRIES_FOR_MONTH = 100;
    const COUNT_OF_CALENDARS         = 5;
    const MAX_COUNT_OF_REMINDERS     = 4;
    const MAX_REMINDER_HOURS_OFFSET  = 124;
    const MIN_REMINDER_HOURS_OFFSET  = 1;

    /**
     * Stores reminders for given loop iteration
     * This is needed as the reminder has unique key (date-reminder)
     *
     * @var array $remindersForIteration
     */
    private $remindersForIteration = [];

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
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $calendars = $this->addCalendars($manager);

        $this->addSchedulesForPreviousMonth($manager, $calendars);
        $this->addSchedulesForCurrentMonth($manager, $calendars);
        $this->addSchedulesForNextMonth($manager, $calendars);
    }

    /**
     * @param ObjectManager $manager
     * @param MyScheduleCalendar[] $calendars
     * @throws Exception
     */
    private function addSchedulesForPreviousMonth(ObjectManager $manager, array $calendars): void
    {
        $startDateTime = $this->faker->dateTimeThisMonth->modify("-1 MONTHS");
        $this->addSchedulesForDateTimeString($startDateTime, $manager, $calendars);
    }

    /**
     * @param ObjectManager $manager
     * @param MyScheduleCalendar[] $calendars
     * @throws Exception
     */
    private function addSchedulesForCurrentMonth(ObjectManager $manager, array $calendars): void
    {
        $startDateTime = $this->faker->dateTimeThisMonth;
        $this->addSchedulesForDateTimeString($startDateTime, $manager, $calendars);
    }

    /**
     * @param MyScheduleCalendar[] $calendars
     * @param ObjectManager $manager
     * @throws Exception
     */
    private function addSchedulesForNextMonth(ObjectManager $manager, array $calendars): void
    {
        $startDateTime = $this->faker->dateTimeThisMonth->modify("+1 MONTHS");
        $this->addSchedulesForDateTimeString($startDateTime, $manager, $calendars);
    }

    /**
     * @param DateTime $startDateTime
     * @param ObjectManager $manager
     * @param MyScheduleCalendar[] $calendars
     * @throws Exception
     */
    private function addSchedulesForDateTimeString(DateTime $startDateTime, ObjectManager $manager, array $calendars): void
    {
        for($x = 0; $x <= self::COUNT_OF_ENTRIES_FOR_MONTH; $x++){
            $randomCalendarIndex = array_rand($calendars);
            $calendar            = $calendars[$randomCalendarIndex];

            $firstDayInMonth = 1;
            $lastDayInMonth  = $startDateTime->format("t");
            $dayInMonth      = rand($firstDayInMonth, $lastDayInMonth);

            $startDateTimeString = $startDateTime->format("Y-m-") . $dayInMonth . " " . $this->faker->time();
            $startDateTime       = new DateTime($startDateTimeString);

            $durationTime = rand(1,5);

            $endDateTime = clone $startDateTime;
            $endDateTime->modify("+{$durationTime} HOUR");
            $location = $this->faker->postcode . " " . $this->faker->city . ", " . $this->faker->streetAddress;

            $schedule = new MySchedule();
            $schedule->setDeleted(false);
            $schedule->setBody($this->faker->sentence);
            $schedule->setCalendar($calendar);
            $schedule->setAllDay(false);
            $schedule->setTitle($this->faker->sentence);
            $schedule->setStart($startDateTime);
            $schedule->setEnd($endDateTime);
            $schedule->setCategory(MySchedule::CATEGORY_TIME);
            $schedule->setLocation($location);

            $countOfReminders = rand(0, self::MAX_COUNT_OF_REMINDERS);
            $dateForReminder  = new DateTimeImmutable($startDateTime->format("Y-m-d H:i:s")); // required to prevent modifying already existing date times
            for($y = 0; $y < $countOfReminders; $y++){

                $dateForReminder                   = $this->getUniqueDateForReminder($dateForReminder, $x);
                $this->remindersForIteration[$x][] = $dateForReminder->format("Y-m-d H:i:s");

                $reminder = new MyScheduleReminder();
                $reminder->setSchedule($schedule);
                $reminder->setDate($dateForReminder);
                $manager->persist($reminder);
            }

            $manager->persist($schedule);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return MyScheduleCalendar[]
     */
    private function addCalendars(ObjectManager $manager): array
    {
        $allCalendars = [];
        for($x = 0; $x <= self::COUNT_OF_CALENDARS; $x++){
            $hexColor = $this->faker->hexColor;
            $calendar = new MyScheduleCalendar();
            $calendar->setColor("white");
            $calendar->setBorderColor($hexColor);
            $calendar->setBackgroundColor($hexColor);
            $calendar->setDragBackgroundColor($hexColor);
            $calendar->setName($this->faker->word);
            $calendar->setDeleted(false);
            $calendar->setIcon(FontawesomeIconsProvider::getRandomIcon());

            $allCalendars[] = $calendar;

            $manager->persist($calendar);
        }

        $manager->flush();

        return $allCalendars;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 16;
    }

    /**
     * Will generate new date time over and over and over until it's unique date time for reminder
     * @param DateTimeImmutable $dateForReminder
     * @param int $iteration
     * @return DateTimeImmutable
     */
    private function getUniqueDateForReminder(DateTimeImmutable $dateForReminder, int $iteration): DateTimeImmutable
    {
        $randomHoursOffset = rand(self::MIN_REMINDER_HOURS_OFFSET, self::MAX_REMINDER_HOURS_OFFSET);
        $dateForReminder->modify("-{$randomHoursOffset} HOURS");

        while(
                array_key_exists($iteration, $this->remindersForIteration)
            &&  in_array($dateForReminder->format("Y-m-d H:i:s"), $this->remindersForIteration[$iteration])
        ){
            // this is required as DateTimeImmutable is being used
            $dateForReminder = $dateForReminder->modify("-{$randomHoursOffset} HOURS");
        }

        return $dateForReminder;
    }
}
