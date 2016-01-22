<?php
/**
 * Phoenix Laboratories NG.
 * Author: J. C. Nwobodo (jc.nwobodo@gmail.com)
 * Project: RBHCISTD
 * Date:    1/22/2016
 * Time:    9:13 AM
 **/

namespace Application\Models\Mappers;

use Application\Models;
use System\Utilities\DateTime;
use Application\Models\Collections\ConsultationCollection;

class ConsultationMapper extends Mapper
{
    public function __construct()
    {
        parent::__construct();
        $this->selectStmt = self::$PDO->prepare("SELECT * FROM app_consultations WHERE id=?");
        $this->selectAllStmt = self::$PDO->prepare("SELECT * FROM app_consultations");
        $this->selectByDoctorStmt = self::$PDO->prepare("SELECT * FROM app_consultations WHERE doctor=?");
        $this->selectByPatientStmt = self::$PDO->prepare("SELECT * FROM app_consultations WHERE patient=?");
        $this->selectByStatusStmt = self::$PDO->prepare("SELECT * FROM app_consultations WHERE status=?");
        $this->updateStmt = self::$PDO->prepare("UPDATE app_consultations SET doctor=?, patient=?, meeting_date=?, start_time=?, end_time=?, status=?  WHERE id=?");
        $this->insertStmt = self::$PDO->prepare("INSERT INTO app_consultations (doctor,patient,meeting_date,start_time,end_time,status) VALUES (?,?,?,?,?,?)");
        $this->deleteStmt = self::$PDO->prepare("DELETE FROM app_consultations WHERE id=?");
    }

    public function findByDoctor($doctor_id)
    {
        $this->selectByDoctorStmt->execute( array($doctor_id) );
        $raw_data = $this->selectByDoctorStmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->getCollection( $raw_data );
    }

    public function findByPatient($patient_id)
    {
        $this->selectByPatientStmt->execute( array($patient_id) );
        $raw_data = $this->selectByPatientStmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->getCollection( $raw_data );
    }

    public function findByStatus($status)
    {
        $this->selectByStatusStmt->execute( array($status) );
        $raw_data = $this->selectByStatusStmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->getCollection( $raw_data );
    }

    protected function targetClass()
    {
        return "Application\\Models\\Consultation";
    }

    protected function getCollection( array $raw )
    {
        return new ConsultationCollection( $raw, $this );
    }

    protected function doCreateObject( array $array )
    {
        $class = $this->targetClass();
        $object = new $class($array['id']);
        $doctor = Models\Doctor::getMapper('Doctor')->find($array['doctor']);
        if(is_object($doctor)) $object->setDoctor($doctor);
        $patient = Models\Patient::getMapper('Patient')->find($array['patient']);
        if(is_object($patient)) $object->setPatient($patient);
        $object->setMeetingDate(DateTime::getDateTimeObjFromInt($array['meeting_date']));
        $object->setStartTime(DateTime::getDateTimeObjFromInt($array['start_time']));
        $object->setEndTime(DateTime::getDateTimeObjFromInt($array['end_time']));
        $object->setStatus($array['status']);

        return $object;
    }

    protected function doInsert(Models\DomainObject $object )
    {
        $values = array(
            is_object($object->getDoctor()) ? $object->getDoctor()->getId() : NULL,
            is_object($object->getPatient()) ? $object->getPatient()->getId() : NULL,
            $object->getMeetingDate()->getDateTimeInt(),
            $object->getStartTime()->getDateTimeInt(),
            $object->getEndTime()->getDateTimeInt(),
            $object->getStatus()
        );
        $this->insertStmt->execute( $values );
        $id = self::$PDO->lastInsertId();
        $object->setId( $id );
    }

    protected function doUpdate(Models\DomainObject $object )
    {
        $values = array(
            is_object($object->getDoctor()) ? $object->getDoctor()->getId() : NULL,
            is_object($object->getPatient()) ? $object->getPatient()->getId() : NULL,
            $object->getMeetingDate()->getDateTimeInt(),
            $object->getStartTime()->getDateTimeInt(),
            $object->getEndTime()->getDateTimeInt(),
            $object->getStatus(),
            $object->getId()
        );
        $this->updateStmt->execute( $values );
    }

    protected function doDelete(Models\DomainObject $object )
    {
        $values = array( $object->getId() );
        $this->deleteStmt->execute( $values );
    }

    protected function selectStmt()
    {
        return $this->selectStmt;
    }

    protected function selectAllStmt()
    {
        return $this->selectAllStmt;
    }
}