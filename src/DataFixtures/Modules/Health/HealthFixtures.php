<?php

namespace App\DataFixtures\Modules\Health;

use App\DTO\Modules\Health\DoctorContactDto;
use App\Entity\Modules\Health\Doctor;
use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Repository\Modules\Storage\StorageFileRepository;
use App\Traits\SerializerAwareTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\RandomException;

class HealthFixtures extends Fixture implements OrderedFixtureInterface
{
    use SerializerAwareTrait;

    private const int DOCTOR_AND_ILLNESS_COUNT   = 6;
    private const int MAX_APPOINTMENT_PER_DOCTOR = 4;

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct(
        private readonly StorageFileRepository $storageFileRepository
    ) {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $doctors      = $this->handleDoctors($manager);
        $illnesses    = $this->handleIllnesses($manager);
        $appointments = $this->handleAppointments($illnesses, $doctors, $manager);

        $this->handleFiles($appointments, $manager);
    }

    /**
     * @param DoctorAppointment[] $appointments
     * @param ObjectManager $manager
     */
    private function handleFiles(array $appointments, ObjectManager $manager): void
    {
        foreach ($appointments as $appointment) {
            $files = $this->storageFileRepository->getCount(random_int(1, 3));
            $appointment->setStorageFiles($files);
            $manager->persist($appointment);
        }

        $manager->flush();
    }

    /**
     * @param Illness[]     $illnesses
     * @param Doctor[]      $doctors
     * @param ObjectManager $manager
     *
     * @return array
     */
    private function handleAppointments(array $illnesses, array $doctors, ObjectManager $manager): array
    {
        $appointments = [];
        foreach ($illnesses as $illness) {
            $doctor = array_pop($doctors);
            for ($x = 1; $x <= self::MAX_APPOINTMENT_PER_DOCTOR; $x++) {
                $appointment = new DoctorAppointment();
                $appointment->setIllness($illness);
                $appointment->setDoctor($doctor);
                $appointment->setInformation($this->faker->sentence(8));
                $appointment->setDate($this->faker->dateTime());

                $manager->persist($appointment);
                $appointments[] = $appointment;
            }

            $illness->setAppointments($appointments);
            $manager->persist($illness);
        }

        $manager->flush();

        return $appointments;
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Illness[]
     * @throws RandomException
     */
    private function handleIllnesses(ObjectManager $manager): array
    {
        $illnesses = [];
        for ($x = 1; $x <= self::DOCTOR_AND_ILLNESS_COUNT; $x++) {
            $illness = new Illness();

            $illness->setInformation($this->faker->sentence(random_int(24, 60)));
            $illness->setName($this->faker->sentence(1));
            $manager->persist($illness);
            $illnesses[] = $illness;
        }

        $manager->flush();

        return $illnesses;
    }

    /**
     * @return Doctor[]
     */
    private function handleDoctors(ObjectManager $manager): array
    {
        $doctors = [];
        for ($x = 1; $x <= self::DOCTOR_AND_ILLNESS_COUNT; $x++) {
            $doctor = new Doctor();
            $doctor->setAddress($this->faker->address);
            $doctor->setInformation($this->faker->sentence(8));
            $doctor->setName($this->faker->name);
            $doctor->setSpecialisation($this->faker->sentence(1));

            $contacts = [
                json_decode($this->serialize(new DoctorContactDto( "Tel.", $this->faker->phoneNumber, $this->faker->uuid)), true),
                json_decode($this->serialize(new DoctorContactDto( "Email", $this->faker->companyEmail, $this->faker->uuid)), true),
            ];

            $doctor->setContacts($contacts);

            $manager->persist($doctor);
            $doctors[] = $doctor;
        }

        $manager->flush();

        return $doctors;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 22;
    }

}
