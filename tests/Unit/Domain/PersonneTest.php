<?php

namespace App\Tests\Unit\Domain;

use App\Domain\Absence;
use App\Domain\AbsenceType;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\AbsenceRepository;
use PHPUnit\Framework\TestCase;

/**
 * Unit test case of @see Personne class.
 *
 * @author Vlad Riabchenko <vriabchenko@laposte.fr>
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
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        new Personne('', '', $personneRepository, $absenceRepository);
    }

    /**
     * @see Personne::getEmail() returns the email initialized in @see Personne::__constructor().
     */
    public function testReturnsEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('rick.sanchez@laposte.fr', '', $personneRepository, $absenceRepository);

        $this->assertEquals('rick.sanchez@laposte.fr', $personne->getEmail());
    }

    /**
     * @see Personne::getNom() returns the email initialized in @see Personne::__constructor().
     */
    public function testReturnsNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('', 'Sanchez', $personneRepository, $absenceRepository);

        $this->assertEquals('Sanchez', $personne->getNom());
    }

    /**
     * @see Personne::update() throws no exception when the email passed to it
     * equals the email already initialized in @see Personne::$email
     * even if @see PersonneRepositoryInterface::emailAlreadyExist() returns `true`.
     */
    public function testUpdateThrowsNoEmailAlreadyTakenExceptionOnSameEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, true);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('rick.sanchez@laposte.fr', '', $personneRepository, $absenceRepository);
        $personne->update('rick.sanchez@laposte.fr', '');

        $this->addToAssertionCount(1);
    }

    /**
     * @see Personne::update() throws an exception when the email passed to it
     * is already taken according to @see PersonneRepositoryInterface::emailAlreadyExist().
     * Email passed to @see Personne::update() must differ from the email already
     * initialized in @see Personne::$email.
     *
     * @expectedException \App\Domain\Exception\EmailAlreadyTakenException
     */
    public function testUpdateThrowsEmailAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, true);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('rick.sanchez@laposte.fr', '', $personneRepository, $absenceRepository);
        $personne->update('morty.smith@laposte.fr', '');
    }

    /**
     * @see Personne::update() updates @see Personne::$email.
     *
     * @author Vlad Riabchenko <vriabchenko@laposte.fr>
     */
    public function testUpdatesEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('rick.sanchez@laposte.fr', '', $personneRepository, $absenceRepository);
        $personne->update('rsanchez@laposte.fr', '');

        $this->assertEquals('rsanchez@laposte.fr', $personne->getEmail());
    }

    /**
     * @see Personne::update() updates @see Personne::$nom.
     *
     * @author Vlad Riabchenko <vriabchenko@laposte.fr>
     */
    public function testUpdatesNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);

        $personne = new Personne('rick.sanchez@laposte.fr', 'Rick', $personneRepository, $absenceRepository);
        $personne->update('rick.sanchez@laposte.fr', 'Richard');

        $this->assertEquals('Richard', $personne->getNom());
    }

    /**
     * @see Personne::deposerAbsence() throws an exeption if absence already exists
     * for given dates according to @see AbsenceRepository::absenceAlreadyExist().
     *
     * @expectedException \App\Domain\Exception\AbsenceAlreadyTakenException
     */
    public function testDeposerAbsenceThrowsAbsenceAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);

        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceRepository->method('absenceAlreadyExist')->willReturn(true);

        $personne = new Personne('', '', $personneRepository, $absenceRepository);
        $personne->deposerAbsence(new \DateTimeImmutable(), new \DateTimeImmutable(), AbsenceType::MALADIE);
    }

    /**
     * @see Personne::deposerAbsence() creates a new @see Absence.
     */
    public function testDeposerAbsenceIsAdded()
    {
        $testCase = $this;
        $debut = new \DateTimeImmutable();
        $fin = new \DateTimeImmutable();

        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);

        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceRepository->method('absenceAlreadyExist')->willReturn(false);

        $absenceRepository->expects($this->once())->method('save')->will($this->returnCallback(function($absence) use ($testCase, $debut, $fin) {
            /** @var $absence Absence */

            $testCase->assertInstanceOf(Absence::class, $absence);
            $testCase->assertInstanceOf(AbsenceType::class, $absence->getType());
            $testCase->assertEquals(AbsenceType::MALADIE, $absence->getType()->getType());
            $testCase->assertEquals($debut, $absence->getDebut());
            $testCase->assertEquals($fin, $absence->getFin());
        }));

        $personne = new Personne('', '', $personneRepository, $absenceRepository);
        $personne->deposerAbsence($debut, $fin, AbsenceType::MALADIE);
    }
}
