<?php
namespace Tests\Feature;

use App\Models\Companies;
use App\Models\Meetings;
use App\Models\Prospeccion;
use App\Models\User;
use App\Models\WSInconcertLogs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MissedMeetingsTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  public $dataCall = [];
  public $dataTicket = [];

  protected function setUp(): void
  {
      parent::setUp(); // TODO: Change the autogenerated stub
      Companies::factory()->create();

      Sanctum::actingAs(
          User::factory()->create(),
          ['environment:dev']
      );
  }

  /**
   * Test a console command.
   *
   * @return void
   */
  public function test_console_command()
  {
    Http::fake([
      env('inconcertWS') => Http::response([
        'status' => true,
        'description' => "OK",
        'data' => [
          "status" => "new",
          "contactId" => "contactId"
        ]
      ], 200)
    ]);

    $this->dataTicket = [
      'datosSugarCRM' => [
        'numero_identificacion' => $this->faker->numerify('##########'),
        'tipo_identificacion' => 'C',
        'email' => 'frvr@gmail.com',
        'user_name' => 'XI_VALDES',
        'nombres' => 'Manuel Álvaro',
        'apellidos' => 'Torres',
        'celular' => '0987519726',
        'telefono' => '022072826',
        'estado' => '1',
        'motivo_cierre' => 'no_contesta',
        'linea_negocio' => '2',
        'tipo_transaccion' => '1',
        'marca' => '1',
        'modelo' => '1',
        'anio' => '2020',
        'placa' => 'PCY7047',
        'kilometraje' => '190000',
        'color' => 'GRIS',
        'asunto' => 'molestias',
        'medio' => '5',
        'id_interaccion_inconcert' => 'id_interaccion_inconcert',
        'comentario_cliente' => 'comentario_cliente',
        'description' => 'description',
        'fuente_descripcion' => 'Tipo WebChat'
      ]
    ];

    $response = $this->json('POST', $this->baseUrl . 'tickets', $this->dataTicket);
    $content = json_decode($response->content());

    $this->dataCall = [
      'datosSugarCRM' => [
        'user_name_asesor' => 'CG_RAMOS', //10296f94-ebf3-42a8-a42d-5c880a18abca
        'user_name_call_center' => 'XI_VALDES', //2fa28a3f-9a39-3d63-4729-5b7353ef1fd9 //2fa28a3f-9a39-3d63-4729-5b7353ef1fd9
        'date_start' => '2021-12-24 19:59',
        'duration_hours' => '0',
        'duration_minutes' => '10',
        'status' => 'Held',
        'direction' => 'Inbound',
        'type' => 'cita',
        'category' => '2',
        'notes' => 'Llamar el día lunes',
        'medio' => '5',
        'ticket' => [
          'id' => $content->data->ticket_id,
          'is_closed' => false
        ],
        'meeting' => [
          'status' => 'Held',
          'date' => '2021-03-31 09:59',
          'duration_hours' => '0',
          'duration_minutes' => '2',
          'subject' => 'Prueba de Manejo',
          'comments' => 'El cliente se acerca a la agencia...',
          'location' => 'Agencia los Chillos',
          'type' => '1',
          'visit_type' => '1',
          'linea_negocio' => '2',
          'client' => [
            'tipo_identificacion' => 'C',
            'numero_identificacion' => '1719932079003',
            'gender' => 'M',
            'names' => 'Manuelito',
            'surnames' => 'Torres',
            'cellphone_number' => '0987512224',
            'phone_home' => '022450251',
            'email' => 'fredd2021@hotmail.com'
          ]
        ]
      ]
    ];

    Prospeccion::where('numero_identificacion', '1719932079003')
      ->update(['estado' => 7]);

    $meetingsPlanned = new Meetings();
    $meetingsPlanned->setConnection('sugar_dev')->where('status', 'Planned')->update(['status' => 'Not Held']);

    $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
    $contentMeeting = json_decode($response->content());

    $this->artisan('set:missedMeetings');
    Http::assertSentCount(2);

    $totalMeetingsPlanned = $meetingsPlanned->setConnection('sugar_dev')->where('status', 'Planned')->count();

    $this->assertEquals(0, $totalMeetingsPlanned);

    Http::assertSent(function (Request $request) use ($contentMeeting) {
     if($request["serviceToken"] === '47bf66e813b446a3c99639f40e28f2b9') {
       return $request->url() == env('inconcertWS') &&
       $request['serviceAction'] == 'c2c' &&
       $request['contactData']['numero_identificacion'] == '1719932079003' &&
       $request['contactData']['tipo_identificacion'] == 'C' &&
       $request['contactData']['email'] == 'fredd2021@hotmail.com' &&
       $request['contactData']['firstname'] == 'Manuelito' &&
       $request['contactData']['lastname'] == 'Torres' &&
       $request['contactData']['tipo_transaccion'] == "1" &&
       $request['contactData']['linea_negocio'] == "2" &&
       $request['contactData']['ProspeccionId'] == $contentMeeting->data->prospeccion_id &&
       $request['contactData']['user_name_asesor'] == '10296f94-ebf3-42a8-a42d-5c880a18abca' &&
       $request['contactData']['user_name_call_center'] == '2fa28a3f-9a39-3d63-4729-5b7353ef1fd9' &&
       $request['contactData']['date_start'] == '2021-12-24 19:59:00' &&
       $request['contactData']['type'] == 'cita' &&
       $request['contactData']['category_id'] == '2' &&
       $request['contactData']['notes'] == 'Llamar el día lunes' &&
       $request['contactData']['meeting_date'] == '2021-03-31 09:59:00' &&
       $request['contactData']['meeting_subject'] == 'Prueba de Manejo: El cliente se acerca a la agencia...' &&
       $request['contactData']['meeting_duration_hours'] == '0' &&
       $request['contactData']['meeting_duration_minutes'] == '2' &&
       $request['contactData']['meeting_comments'] == null &&
       $request['contactData']['meeting_location_id'] == 'Agencia los Chillos' &&
       $request['contactData']['meeting_type_id'] == '1' &&
       $request['contactData']['meeting_visit_type_id'] == '1' &&
       $request['contactData']['meeting_linea_negocio_id'] == '2' &&
       $request['contactData']['meeting_marca_id'] == '1' &&
       $request['contactData']['meeting_modelo_id'] == '1' &&
       $request['contactData']['meeting_client_tipo_identificacion_id'] == 'C' &&
       $request['contactData']['meeting_client_numero_identificacion'] == '1719932079003' &&
       $request['contactData']['meeting_client_names'] == 'Manuelito' &&
       $request['contactData']['meeting_client_surnames'] == 'Torres' &&
       $request['contactData']['meeting_client_cellphone_number'] == '0987512224' &&
       $request['contactData']['meeting_client_email'] == 'fredd2021@hotmail.com' &&
       $request['contactData']['phone'] == '0987512224' &&
       $request['contactData']['mobile'] == '0987512224' &&
       $request['contactData']['language'] == 'es' &&
       $request['contactData']['country'] == 'EC';
     }

      return $request->url() == env('inconcertWS');
    });

    $wsLogInconcert = WSInconcertLogs::where('prospeccion_id', $contentMeeting->data->prospeccion_id)->first();
    $this->assertEquals('OK', $wsLogInconcert->description);
    $this->assertEquals('contactId', $wsLogInconcert->contact_id);
  }
}
