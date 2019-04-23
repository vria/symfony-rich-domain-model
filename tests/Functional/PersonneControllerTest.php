<?php

namespace App\Tests\Functional;

use App\Application\Controller\PersonneController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see PersonneController
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneControllerTest extends WebTestCase
{
    /**
     * @see PersonneController::lister()
     */
    public function testLister()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertCount(
            3,
            $crawler->filter('table tbody tr'),
                'Le tableau des personnes est vide'
        );

        $boutonAjouter = $crawler->filter('a[href="/personne"]:contains("Ajouter")');
        $this->assertCount(1, $boutonAjouter, 'Bouton "Ajouter" est présente sur la page');
    }

    /**
     * @see PersonneController::creer()
     *
     * @depends testLister
     */
    public function testCreer()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/personne');

        $form = $crawler->selectButton('Ajouter')->form([
            'creer_personne[email]' => 'jsmith@webnet.fr',
            'creer_personne[nom]' => 'Jerry',
        ]);

        $client->submit($form);

        $this->assertEquals(
            '/',
            $client->getResponse()->headers->get('location'),
            'Redirigé vers la page /personne après la création d\'une nouvelle personne'
        );

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->headers->get('location'));
        $crawler = $client->followRedirect();

        $this->assertCount(
            4,
            $crawler->filter('table tbody tr'),
            'Le tableau des personnes contient la personne qui vient d\'être ajoutée'
        );

        $this->assertEquals(
            'jsmith@webnet.fr',
            $crawler->filter('table tbody tr')->eq(1)->filter('td')->eq(0)->text(),
            'La personne ajouté a un email assigné'
        );

        $this->assertEquals(
            'Jerry',
            $crawler->filter('table tbody tr')->eq(1)->filter('td')->eq(1)->text(),
            'La personne ajouté a un nom assigné'
        );
    }

    /**
     * @see PersonneController::modifier()
     *
     * @depends testCreer
     */
    public function testModifier()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/personne/rsanchez@webnet.fr');

        $this->assertEquals(
            'rsanchez@webnet.fr',
            $crawler->filter('input[name="creer_personne[email]"]')->attr('value'),
            'Champ "Email" contient le mail de la personne'
        );

        $this->assertEquals(
            'Rick',
            $crawler->filter('input[name="creer_personne[nom]"]')->attr('value'),
            'Champ "Nom" contient le nom de la personne'
        );

        $form = $crawler->selectButton('Modifier')->form([
            'creer_personne[nom]' => 'Sanchez',
        ]);

        $client->submit($form);

        $this->assertEquals(
            '/',
            $client->getResponse()->headers->get('location'),
            'Redirigé vers la page "/" après la modification d\'une personne'
        );

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->headers->get('location'));
        $crawler = $client->followRedirect();

        $this->assertCount(
            3,
            $crawler->filter('table tbody tr'),
            'Le tableau des personnes contient le même nombre des personnes après la modification'
        );

        $this->assertEquals(
            'rsanchez@webnet.fr',
            $crawler->filter('table tbody tr')->eq(2)->filter('td')->eq(0)->text(),
            'L\'email d\'une personne modifiée reste inchangée'
        );

        $this->assertEquals(
            'Sanchez',
            $crawler->filter('table tbody tr')->eq(2)->filter('td')->eq(1)->text(),
            'La personne modifiée a un bon nom'
        );
    }
}