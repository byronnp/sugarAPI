<?php

namespace Tests\Feature;

use App\Models\Companies;
use App\Models\Tickets;
use App\Models\User;
use App\Models\Ws_logs;
use App\Services\TicketClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class notAnswerTicketTest extends TestCase
{
    use RefreshDatabase, WithFaker;
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
     * Ticket not answere Success
     *
     * @return void
     */
    public function test_not_answer_ticket()
    {
        $ticketClass = new TicketClass();
        $ticketClass->estado = '1';
        $ticketClass->numero_identificacion = '1711932074';
        $ticketClass->precio_c = '25000';
        $ticketClass->combustible_c = 'gasolina';
        $ticketClass->kilometraje_c = '30000';
        $ticketClass->anio_min_c = '2019';
        $ticketClass->anio_max_c = '2020';
        $ticketClass->color_c = 'negro';
        $ticketClass->marca_c = '18';
        $ticketClass->modelo_c = '110';
        $ticket = $ticketClass->create();

        $response = $this->json('POST', $this->baseUrl . 'not_answer_ticket/'. $ticket->id);

        $content_update = json_decode($response->content());
        $response->assertStatus(200);
        $this->assertEquals($content_update->data->ticket_id, $ticket->id);
        $this->assertEquals($content_update->data->ticket_name, $ticket->name);

        $ticketUpdate = Tickets::find($ticket->id);
        $statusEnGestion = 4;
        $this->assertEquals($statusEnGestion, $ticketUpdate->estado);

        $wsLogs = Ws_logs::where('route', 'api/not_answer_ticket/'. $ticket->id)->first();

        $this->assertEquals($ticket->id, $wsLogs->ticket_id);
        $this->assertEquals('sugar_dev', $wsLogs->environment);
        $this->assertEquals('tests_source', $wsLogs->source);
    }

    public function test_not_answer_ticket_invalid()
    {
        $response = $this->json('POST', $this->baseUrl . 'not_answer_ticket/notExistId');

        $content_update = json_decode($response->content());

        $response->assertStatus(404);
        $this->assertEquals($content_update->error, 'Ticket no existe, id inválido');
    }

}
