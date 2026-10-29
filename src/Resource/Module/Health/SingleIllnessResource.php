<?php

namespace App\Resource\Module\Health;

use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Entity\Modules\Storage\StorageFile;

class SingleIllnessResource
{
    /**
     * @param Illness $illness
     *
     * @return array
     */
    public static function toArray(Illness $illness): array
    {
        return [
            'id'           => $illness->getId(),
            'information'  => $illness->getInformation(),
            'name'         => $illness->getName(),
            'appointments' => array_map(fn(DoctorAppointment $doctorAppointment): array => [
                'id'           => $doctorAppointment->getId(),
                'date'         => $doctorAppointment->getDate()->format("Y-m-d H:i:s"),
                'information'  => $doctorAppointment->getInformation(),
                'doctor'       => [
                    'id'   => $doctorAppointment->getDoctor()->getId(),
                    'name' => $doctorAppointment->getDoctor()->getName(),
                ],
                'storageFiles' => array_map(fn(StorageFile $file): array => [
                    'id'                    => $file->getId(),
                    'filePath'              => $file->getFilePath(),
                    'fileNameWithExtension' => $file->getFileNameWithExtension(),
                ], $doctorAppointment->getStorageFiles()),
            ], $illness->getAppointments()->toArray()),
        ];
    }
}