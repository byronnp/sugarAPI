<?php

namespace Tests\Feature;

use App\Models\Prospeccion;
use App\Models\User;
use App\Models\Ws_logs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WSLogsTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    public $data = [];
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUserSanctum();

        $this->dataTicket = [
            'datosSugarCRM' => [
                'numero_identificacion' => $this->faker->numerify('##########'),
                'tipo_identificacion' => 'C',
                'email' => 'frvr@gmail.com',
                'user_name' => 'CG_RAMOS',
                'nombres' => 'FREDDY ROBERTO',
                'apellidos' => 'VARGAS RODRIGUEZ',
                'celular' => '0987519726',
                'telefono' => '022072826',
                'estado' => '1',
                'medio' => '5',
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
                'id_interaccion_inconcert' => 'id_interaccion_inconcert',
                'comentario_cliente' => 'comentario_cliente',
                'description' => 'description',
                'fuente_descripcion' => 'Tipo WebChat'
            ],

            "datos_adicionales" => [
                "adicional1" => "data1",
                "adicional2" => "data2"
            ]
        ];

        $this->dataCall = [
            'datosSugarCRM' => [
                'user_name_asesor' => 'CG_RAMOS', //10296f94-ebf3-42a8-a42d-5c880a18abca
                'user_name_call_center' => 'RR_TORRES', //15396f5c-dbf1-5a6c-9637-5a8b8f14aaf3
                'date_start' => '2021-12-24 19:59',
                'duration_hours' => '0',
                'duration_minutes' => '10',
                'status' => 'Held',
                'direction' => 'Inbound',
                'type' => 'cita',
                'category' => '2',
                'medio' => '6',
                'notes' => 'Llamar el día lunes',
                'ticket' => [
                    'id' => 'id_ticket',
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
                    'linea_negocio' => '1',
                    'client' => [
                        'tipo_identificacion' => 'C',
                        'numero_identificacion' => '1719932079001',
                        'gender' => 'M',
                        'names' => 'Freddy',
                        'surnames' => 'Vargas',
                        'cellphone_number' => '1234567890',
                        'phone_home' => '022072827',
                        'email' => 'fredd2021@hotmail.com'
                    ]
                ]
            ],
            "datos_adicionales" => [
                "adicional1" => "data1",
                "adicional2" => "data2"
            ]
        ];
    }

    /** @test */
    public function saveWSLogTicketRoute()
    {
        $response = $this->json('POST', $this->baseUrl . 'tickets', $this->dataTicket);
        $content = json_decode($response->content());
        $wsLog = Ws_logs::where('ticket_id', $content->data->ticket_id)
            ->where('route', 'api/tickets/')
            ->first();

        $this->assertEquals($content->data->interaction_id, $wsLog->interaccion_id);
        $this->assertJson(json_encode($this->dataTicket['datosSugarCRM']), $wsLog->datos_sugar_crm);
        $this->assertJson(json_encode($this->dataTicket['datos_adicionales']), $wsLog->datos_adicionales);
    }

    /** @test */
    public function saveWSLogCallRoute()
    {
        $response = $this->json('POST', $this->baseUrl . 'tickets', $this->dataTicket);
        $content = json_decode($response->content());
        $this->dataCall['datosSugarCRM']['ticket']['id'] = $content->data->ticket_id;

        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
        $content = json_decode($response->content());

        $wsLog = Ws_logs::where('ticket_id', $content->data->ticket_id)
            ->where('route', 'api/calls/')
            ->first();
        $this->assertJson(json_encode($this->dataCall['datosSugarCRM']), $wsLog->datos_sugar_crm);
        $this->assertJson(json_encode($this->dataCall['datos_adicionales']), $wsLog->datos_adicionales);
    }
}
