<?php

namespace App\Tests\Unit\Domain;

use App\Domain\Absence;
use App\Domain\AbsenceType;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit test case of @see Personne class.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneTest extends TestCase
{
    /**
     * @see Personne constructor throws an exception if email is already taken.
     *
     * @expectedException \App\Domain\Exception\EmailAlreadyTakenException
     */
    public function testThrowsEmailAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(true);

        new Personne('', '', $personneRepository);
    }

    /**
     * @see Personne::getEmail() returns the email initialized in @see Personne::__constructor().
     */
    public function testReturnsEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);

        $personne = new Personne('rick.sanchez@webnet.fr', '', $personneRepository);

        $this->assertEquals('rick.sanchez@webnet.fr', $personne->getEmail());
    }

    /**
     * @see Personne::getNom() returns the email initialized in @see Personne::__constructor().
     */
    public function testReturnsNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);

        $personne = new Personne('', 'Sanchez', $personneRepository);

        $this->assertEquals('Sanchez', $personne->getNom());
    }

    /**
     * @see Personne::update() throws no exception when the email passed to it
     * equals the email already initialized in @see Personne::$email
     * even if @see PersonneRepositoryInterface::absenceAlreadyExist() returns `true`.
     */
    public function testUpdateThrowsNoEmailAlreadyTakenExceptionOnSameEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')
            ->willReturnOnConsecutiveCalls(false, true);

        $personne = new Personne('rick.sanchez@webnet.fr', '', $personneRepository);
        $personne->update('rick.sanchez@webnet.fr', '');

        $this->addToAssertionCount(1);
    }

    /**
     * @see Personne::update() throws an exception when the email passed to it
     * is already taken according to @see PersonneRepositoryInterface::absenceAlreadyExist().
     * Email passed to @see Personne::update() must differ from the email already
     * initialized in @see Personne::$email.
     *
     * @expectedException \App\Domain\Exception\EmailAlreadyTakenException
     */
    public function testUpdateThrowsEmailAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, true);

        $personne = new Personne('rick.sanchez@webnet.fr', '', $personneRepository);
        $personne->update('morty.smith@webnet.fr', '');
    }

    /**
     * @see Personne::update() updates @see Personne::$email.
     *
     * @author Vlad Riabchenko <vriabchenko@webnet.fr>
     */
    public function testUpdatesEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, false);

        $personne = new Personne('rick.sanchez@webnet.fr', '', $personneRepository);
        $personne->update('rsanchez@webnet.fr', '');

        $this->assertEquals('rsanchez@webnet.fr', $personne->getEmail());
    }

    /**
     * @see Personne::update() updates @see Personne::$nom.
     *
     * @author Vlad Riabchenko <vriabchenko@webnet.fr>
     */
    public function testUpdatesNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, false);

        $personne = new Personne('rick.sanchez@webnet.fr', 'Rick', $personneRepository);
        $personne->update('rick.sanchez@webnet.fr', 'Richard');

        $this->assertEquals('Richard', $personne->getNom());
    }

    /**
     * @see Personne::deposerAbsence() throws an exeption if absence already exists
     * for given dates according to @see PersonneRepositoryInterface::absenceAlreadyExist().
     *
     * @expectedException \App\Domain\Exception\AbsenceAlreadyTakenException
     */
    public function testDeposerAbsenceThrowsAbsenceAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $personneRepository->method('absenceAlreadyExist')->willReturn(true);

        $personne = new Personne('', '', $personneRepository);
        $personne->deposerAbsence(new \DateTimeImmutable(), new \DateTimeImmutable(), AbsenceType::MALADIE);
    }

    /**
     * @see Personne::deposerAbsence() creates a new @see Absence
     * and adds it to @see Personne::$absences.
     */
    public function testDeposerAbsenceIsAdded()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $personneRepository->method('absenceAlreadyExist')->willReturn(false);

        $personne = new Personne('', '', $personneRepository);
        $personne->deposerAbsence(new \DateTimeImmutable(), new \DateTimeImmutable(), AbsenceType::MALADIE);

        $personneReflectionClass = new \ReflectionClass(Personne::class);
        $absencesReflectionProperty = $personneReflectionClass->getProperty('absences');
        $absencesReflectionProperty->setAccessible(true);

        $absences = $absencesReflectionProperty->getValue($personne);
        $this->assertIsArray($absences);
        $this->assertCount(1, $absences);
        $this->assertInstanceOf(Absence::class, $absences[0]);
    }
}
