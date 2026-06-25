<?php

namespace App\Repository\Modules\Health;

use App\Entity\Modules\Health\DoctorAppointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DoctorAppointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctorAppointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctorAppointment[]    findAll()
 * @method DoctorAppointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoctorAppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctorAppointment::class);
    }
}