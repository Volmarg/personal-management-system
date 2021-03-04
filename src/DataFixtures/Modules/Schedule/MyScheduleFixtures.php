<?php

namespace App\DataFixtures\Modules\Schedule;

use App\Controller\Core\Application;
use App\DataFixtures\Providers\FontawesomeIconsProvider;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Entity\Modules\Schedules\Schedule;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Faker\Factory;

class MyScheduleFixtures extends Fixture implements OrderedFixtureInterface
{
    const COUNT_OF_ENTRIES_FOR_MONTH = 100;
    const COUNT_OF_CALENDARS         = 5;

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

            $schedule = new Schedule();
            $schedule->setDeleted(false);
            $schedule->setBody($this->faker->sentence);
            $schedule->setCalendar($calendar);
            $schedule->setAllDay(false);
            $schedule->setTitle($this->faker->sentence);
            $schedule->setStart($startDateTime);
            $schedule->setEnd($endDateTime);
            $schedule->setCategory(Schedule::CATEGORY_TIME);
            $schedule->setLocation($location);

            $manager->persist($schedule);;
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
}
