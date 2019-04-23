<?php

namespace App\Tests\Functional;

use App\Application\Controller\AbsenceController;
use App\Application\Controller\PersonneController;
use App\Domain\Absence;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see AbsenceControllerTest
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceControllerTest extends WebTestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
        ClockMock::register(Absence::class);
        ClockMock::withClockMock(strtotime("2019-04-25 15:00:00"));
    }

    /**
     * @see AbsenceController::calendrier()
     */
    public function testCalendrier()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/compteurs/jours_disponibles/rsanchez@webnet.fr');

        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Compteurs de Rick")'),
            'La page de jours disponibles contient un entête'
        );

        $rows = $crawler->filter('table tbody tr');

        $this->assertCount(
            2,
            $rows,
            'Deux compteurs d\'absence sont assignés à la personne'
        );

        $this->assertCount(
            1,
            $rows->eq(0)->filter('tr:contains("Congé payé")'),
            'Le premier compteur est "Congé payé"'
        );

        $this->assertCount(
            1,
            $rows->eq(1)->filter('tr:contains("Télétravail")'),
            'Le deuxième compteur est "Télétravail"'
        );
    }
}
