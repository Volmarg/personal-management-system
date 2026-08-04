<?php

namespace App\Services\Module\Health;

use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Entity\Modules\Storage\StorageFile;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

/**
 * This is a generic service for code that does not fit any more specific doctor module concept
 */
class DoctorService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * This saving process is capable of handling multiple appointments-file saving at once.
     * The drawback here is that every single time the DB-id will change - it has 0 impact on functionality tho.
     *
     * @param string|int|null $illnessId
     * @param array           $appointmentsData
     */
    public function handleFiles(string|int|null $illnessId, array $appointmentsData): void
    {
        $illness = $this->em->find(Illness::class, $illnessId);
        foreach ($illness->getAppointments() as $appointment) {
            $appointment->setStorageFiles([]);
            $this->em->persist($appointment);
        }

        foreach ($appointmentsData as $chunk) {
            $storageFileIds = $chunk['storageFileIds'] ?? null;
            $appointmentId  = $chunk['appointmentId'] ?? null;

            if (empty($storageFileIds)) {
                throw new LogicException("One of the chunks if missing storageFileIds");
            }

            if (empty($appointmentId)) {
                throw new LogicException("One of the chunks if missing appointmentId");
            }

            $doctorAppointment = $this->em->find(DoctorAppointment::class, $appointmentId);
            if (is_null($doctorAppointment)) {
                throw new LogicException("No doctor appointment found for id: {$appointmentId}");
            }

            $storageFileIds  = array_unique($storageFileIds);
            $storageFileRepo = $this->em->getRepository(StorageFile::class);
            $storageFiles    = array_filter(array_map(function ($id) use ($storageFileRepo) {
                return $storageFileRepo->find($id);
            }, $storageFileIds));

            if (count($storageFiles) !== count($storageFileIds)) {
                throw new LogicException("Could not find files for all ids: " . json_encode($storageFileIds));
            }

            $doctorAppointment->setStorageFiles($storageFiles);
            $this->em->persist($doctorAppointment);
        }
    }
}