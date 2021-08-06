<?php

namespace Tests\Feature;

use App\Models\Contacts;
use App\Models\Destinations;
use App\Models\DestinationSuggestions;
use App\Models\WsToyotaGo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use Tests\TestCase;

class SumateFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public $dataFormContact = [];
    public $dataFormDestinos = [];
    public $dataFormNegocios = [];

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->dataFormContact = [
            'First Name' => $this->faker->firstName,
            'Last Name' => $this->faker->lastName,
            'Cedula' => $this->faker->numerify('##########'),
            'E-mail Address' => $this->faker->email,
            'Cell Phone' => $this->faker->numerify('##########'),
            'Fecha de nacimiento' => $this->faker->date(),
            'Home City' => $this->faker->city,
            'Tienes un Toyota' => $this->faker->boolean,
            'Modelo' => 'Fortuner',
            'Ciudad y concesionario' => '01',
            'Year' => $this->faker->numerify('####')
        ];
        $this->dataFormDestinos = [
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'email' => $this->faker->email,
            'cedula' => $this->faker->numerify('##########')
        ];
        $this->dataFormNegocios = [
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'email' => $this->faker->email,
            'cedula' => $this->faker->numerify('##########')
        ];
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function sumateForm()
    {
        /*
        $urlSumateForm = "https://crm.toyotago.com.ec/acton/eform/44287/2ab85ccb-1d40-4690-b957-8cf4a6e786e8/d-ext-0001";

        Http::fake([
            $urlSumateForm => Http::response([
                'status' => true
            ], 200)
        ]);
        */

        $response = $this->get('/api/sumate?' . http_build_query($this->dataFormContact));
        $htmlExpected = '<div id="gform_confirmation_message_2" class="gform_confirmation_message_2 gform_confirmation_message" data-gtm-vis-recent-on-screen-47109072_11="5116" data-gtm-vis-first-on-screen-47109072_11="5116" data-gtm-vis-total-visible-time-47109072_11="100" data-gtm-vis-has-fired-47109072_11="1">¡Gracias por contactar con nosotros! Nos pondremos en contacto contigo muy pronto.</div>';
        $this->assertEquals($htmlExpected, $response->content());
        $response->assertStatus(200);

        /*
        $contact = WsToyotaGo::first();
        $this->assertEquals($this->dataFormContact["nombre"], $contact->first_name);
        $this->assertEquals($this->dataFormContact["apellido"], $contact->last_name);
        $this->assertEquals($this->dataFormContact["email"], $contact->email);
        $this->assertEquals($this->dataFormContact["cedula"], $contact->identification);

        Http::assertSent(function (Request $request) use ($urlSumateForm) {
            return $request->url() == $urlSumateForm &&
                $request['First Name'] == $this->dataFormContact["nombre"] &&
                $request['Last Name'] == $this->dataFormContact["apellido"] &&
                $request['E-mail Address'] == $this->dataFormContact["email"] &&
                $request['Cedula'] == $this->dataFormContact["cedula"];
        });
        */
    }

    /**
     *
     */
    public function createDestino()
    {
        //dd(http_build_query($this->data));
        $response = $this->get('/api/destinos?' . http_build_query($this->dataFormDestinos));
        dd($response->content());
        $response->assertStatus(200);

        $contact = Destinations::first();
        $this->assertEquals($this->dataFormDestinos["nombre"], $contact->first_name);
        $this->assertEquals($this->dataFormDestinos["apellido"], $contact->last_name);
        $this->assertEquals($this->dataFormDestinos["email"], $contact->email);
        $this->assertEquals($this->dataFormDestinos["cedula"], $contact->identification);

    }

    /**
     *
     */
    public function createNegocio()
    {
        //dd(http_build_query($this->data));
        $response = $this->get('/api/negocios?' . http_build_query($this->dataFormNegocios));
        dd($response->content());
        $response->assertStatus(200);

        $contact = DestinationSuggestions::first();
        $this->assertEquals($this->dataFormNegocios["nombre"], $contact->first_name);
        $this->assertEquals($this->dataFormNegocios["apellido"], $contact->last_name);
        $this->assertEquals($this->dataFormNegocios["email"], $contact->email);
        $this->assertEquals($this->dataFormNegocios["cedula"], $contact->identification);

    }

    /**
     *
     */
    public function sendDataToActon()
    {
        $urlSumateForm = "https://crm.toyotago.com.ec/acton/eform/44287/2ab85ccb-1d40-4690-b957-8cf4a6e786e8/d-ext-0001";
        $urlDestinosForm = "https://crm.toyotago.com.ec/acton/eform/44287/fadd9b54-c9ac-442f-b7f6-3f6d8a300d74/d-ext-0001";


        $response = $this->get('/api/negocios?' . http_build_query($this->dataFormNegocios));
        dd($response->content());
        $response->assertStatus(200);

    }
}