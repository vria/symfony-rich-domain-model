<?php

namespace App\Tests\Functional;

use App\Application\Controller\AbsenceController;
use App\Domain\Absence;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see AbsenceController
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
        ClockMock::register(AbsenceController::class);
        ClockMock::withClockMock(strtotime("2019-04-25 15:00:00"));
    }

    /**
     * @see AbsenceController::calendrier()
     */
    public function testCalendrierSemaineEnCours()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/calendrier/rsanchez@webnet.fr');

        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Absences de Rick de 22/04/2019 à 28/04/2019")'),
            "Calendrier affiche une semaine en cours par défaut"
        );

        $this->assertCount(
            1,
            $crawler->filter('a[href="/absence/calendrier/rsanchez@webnet.fr/2019-04-15"]:contains("Précédente")'),
            'Une lien vers le calendrier de la semaine précédante est presente'
        );

        $this->assertCount(
            1,
            $crawler->filter('a[href="/absence/calendrier/rsanchez@webnet.fr/2019-04-29"]:contains("Suivante")'),
            'Une lien vers le calendrier de la semaine suivante est presente'
        );

        $calendrier = $crawler->filter('#table-calendrier');

        $this->assertCount(1, $calendrier->filter('tr th')->eq(0)->filter(':contains("22/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(1)->filter(':contains("23/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(2)->filter(':contains("24/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(3)->filter(':contains("25/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(4)->filter(':contains("26/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(5)->filter(':contains("27/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(6)->filter(':contains("28/04")'));

        $absences = $calendrier->filter('tbody tr');
        $this->assertCount(1, $absences, '1 absence est présente');

        $absence = $absences->eq(0)->filter('td[colspan="3"]');
        $this->assertCount(1, $absence, '1 absence de 3 jours est présente');

        $this->assertCount(
            1,
            $absence->filter('a:contains("Congé payé (20/04/2019 - 24/04/2019)")'),
            'Congé est présente'
        );

        $this->assertCount(
            1,
            $absence->filter('a .fa-trash'),
            'Lien d\'annulation du congé est présente'
        );
    }

    /**
     * @see AbsenceController::calendrier()
     */
    public function testCalendrierSemaine20190418()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/calendrier/rsanchez@webnet.fr/2019-04-18');

        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Absences de Rick de 15/04/2019 à 21/04/2019")'),
            "Calendrier affiche une semaine de 15/04/2019 à 21/04/2019"
        );

        $this->assertCount(
            1,
            $crawler->filter('a[href="/absence/calendrier/rsanchez@webnet.fr/2019-04-08"]:contains("Précédente")'),
            'Une lien vers le calendrier de la semaine précédante est presente'
        );

        $this->assertCount(
            1,
            $crawler->filter('a[href="/absence/calendrier/rsanchez@webnet.fr/2019-04-22"]:contains("Suivante")'),
            'Une lien vers le calendrier de la semaine suivante est presente'
        );

        $calendrier = $crawler->filter('#table-calendrier');

        $this->assertCount(1, $calendrier->filter('tr th')->eq(0)->filter(':contains("15/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(1)->filter(':contains("16/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(2)->filter(':contains("17/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(3)->filter(':contains("18/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(4)->filter(':contains("19/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(5)->filter(':contains("20/04")'));
        $this->assertCount(1, $calendrier->filter('tr th')->eq(6)->filter(':contains("21/04")'));

        $absences = $calendrier->filter('tbody tr');
        $this->assertCount(1, $absences, '1 absence est présente');

        $absenceOffset = $absences->eq(0)->filter('td')->eq(0);
        $this->assertEquals(5, $absenceOffset->attr('colspan'));

        $absence = $absences->eq(0)->filter('td')->eq(1);
        $this->assertEquals(2, $absence->attr('colspan'));

        $this->assertCount(
            1,
            $absence->filter('a:contains("Congé payé (20/04/2019 - 24/04/2019)")'),
            'Congé est présente'
        );

        $this->assertCount(
            1,
            $absence->filter('a .fa-trash'),
            'Lien d\'annulation du congé est présente'
        );
    }

    /**
     * @see AbsenceController::calendrier()
     */
    public function testCalendrierNotFoundException()
    {
        $client = static::createClient();
        $client->request('GET', '/absence/calendrier/not_exist@webnet.fr');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposer()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/deposer/rsanchez@webnet.fr');

        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Deposer une absence pour rsanchez@webnet.fr")'),
            ''
        );

        $debutInput = $crawler->filter('input[name="deposer_absence[debut]"]');
        $this->assertCount(1, $debutInput, '');
        $this->assertEquals('2019-04-25', $debutInput->attr('value'), 'Le champ "Début" contient la date de demain par défaut');

        $finInput = $crawler->filter('input[name="deposer_absence[fin]"]');
        $this->assertCount(1, $finInput, '');
        $this->assertEquals('2019-04-25', $finInput->attr('value'), 'Le champ "Fin" contient la date de demain par défaut');
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposerNoCounterCheck()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/deposer/rsanchez@webnet.fr');

        $form = $crawler->selectButton('Déposer')->form([
            'deposer_absence[debut]' => '2019-04-26',
            'deposer_absence[fin]' => '2019-04-27',
            'deposer_absence[type]' => '1',
        ]);

        $client->submit($form);
        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            '/absence/calendrier/rsanchez@webnet.fr',
            $response->headers->get('location'),
            ''
        );
        $crawler = $client->followRedirect();

        $absences = $crawler->filter('#table-calendrier tbody tr');
        $this->assertCount(2, $absences, '2 absences sont présentes');

        $absence = $absences->eq(1)->filter('td[colspan="2"]');
        $this->assertCount(1, $absence, '1 absence de 2 jours est présente');

        $this->assertCount(
            1,
            $absence->filter('a:contains("Maladie (26/04/2019 - 27/04/2019)")'),
            'Congé est présente'
        );

        $this->assertCount(
            1,
            $absence->filter('a .fa-trash'),
            'Lien d\'annulation du congé est présente'
        );
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposerDatesInvalides()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/deposer/rsanchez@webnet.fr');

        $form = $crawler->selectButton('Déposer')->form([
            'deposer_absence[debut]' => '2019-04-26',
            'deposer_absence[fin]' => '2019-04-25',
            'deposer_absence[type]' => '1',
        ]);

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.form-error-message:contains("Date de fin doit être après la date de début")'),
            ''
        );
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposerAbsenceAlreadyTaken()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/deposer/rsanchez@webnet.fr');

        $form = $crawler->selectButton('Déposer')->form([
            'deposer_absence[debut]' => '2019-04-24',
            'deposer_absence[fin]' => '2019-04-25',
            'deposer_absence[type]' => '1',
        ]);

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.form-error-message:contains("Une absence pour ces dates a été déjà déposée")'),
            ''
        );
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposerAbsenceJoursDisponiblesInsuffisants()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/deposer/rsanchez@webnet.fr');

        $form = $crawler->selectButton('Déposer')->form([
            'deposer_absence[debut]' => '2019-04-25',
            'deposer_absence[fin]' => '2019-04-26',
            'deposer_absence[type]' => '2',
        ]);

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.form-error-message:contains("Il ne vous reste plus de jours disponibles pour ce type d\'absence")'),
            ''
        );
    }

    /**
     * @see AbsenceController::modifier()
     */
    public function testModifier()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/absence/modifier/rsanchez@webnet.fr/1');

        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Mettre à jour une absence pour rsanchez@webnet.fr")'),
            ''
        );

        $debutInput = $crawler->filter('input[name="deposer_absence[debut]"]');
        $this->assertCount(1, $debutInput, '');
        $this->assertEquals('2019-04-20', $debutInput->attr('value'), 'Le champ "Début" contient la date 20/04/2019');

        $finInput = $crawler->filter('input[name="deposer_absence[fin]"]');
        $this->assertCount(1, $finInput, '');
        $this->assertEquals('2019-04-24', $finInput->attr('value'), 'Le champ "Fin" contient la date 24/04/2019');

        $finInput = $crawler->filter('select[name="deposer_absence[type]"]');
        $this->assertCount(1, $finInput, '');
        $this->assertEquals(
            '2',
            $finInput->filter('option[selected="selected"]')->attr('value'),
            'Le champ "Type" contient la valeur 2 "Congé payé"'
        );
    }

    /**
     * @see AbsenceController::deposer()
     */
    public function testDeposerNotFoundException()
    {
        $client = static::createClient();
        $client->request('GET', '/absence/deposer/not_exist@webnet.fr');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @see AbsenceController::compteurs()
     */
    public function testCompteurs()
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

    /**
     * @see AbsenceController::compteurs()
     */
    public function testCompteursNotFoundException()
    {
        $client = static::createClient();
        $client->request('GET', '/compteurs/jours_disponibles/not_exist@webnet.fr');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }
}
