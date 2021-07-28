<?php

namespace Tests\Feature;

use App\Models\Calls;
use App\Models\CallsContacts;
use App\Models\CallsCstm;
use App\Models\CallsProspeccion;
use App\Models\Contacts;
use App\Models\ContactsCstm;
use App\Models\EmailAddrBeanRel;
use App\Models\EmailAddreses;
use App\Models\Meetings;
use App\Models\MeetingsContacts;
use App\Models\MeetingsCstm;
use App\Models\Prospeccion;
use App\Models\ProspeccionContacts;
use App\Models\ProspeccionCstm;
use App\Models\ProspeccionMeetings;
use App\Models\Tickets;
use App\Models\TicketsCalls;
use App\Models\TicketsProspeccion;
use App\Models\User;
use App\Models\UsersMeetings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class
CallsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public $dataCall = [];
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUserSanctum();
        $this->setInitDataTicket();

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
                'campania' => '5e686580-ee19-11ea-97ea-000c297d72b1',
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
    }

    /** @test */
    public function postCallSuccesfull()
    {
        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertNotNull($content->data->call_id);
        $this->assertNotNull($content->data->ticket_id);
        $this->assertNotNull($content->data->prospeccion_id);
        $this->assertNotNull($content->data->meeting_id);

        $this->datCallComplete($content->data->call_id, $content->data->ticket_id);
        $this->dataStatusTicket($content->data->ticket_id);
        $this->dataMeetingComplete($content->data->meeting_id);
        $this->dataProspeccionComplete($content->data->prospeccion_id);
        $contact = Contacts::where('deleted', 0)
            ->join('contacts_cstm', 'contacts.id', '=', 'contacts_cstm.id_c')
            ->where('contacts_cstm.numero_identificacion_c', $this->dataCall['datosSugarCRM']['meeting']['client']['numero_identificacion'])
            ->get()->first();

        $this->dataContactComplete($contact);
        $this->dataEmailComplete($contact);
        $this->relationalDataComplete($content, $contact);
    }

    public function dataStatusTicket($ticket_id)
    {
        $ticket = Tickets::find($ticket_id);
        $this->assertEquals($ticket->estado, 5);
    }

    /** @test */
    public function postCallStatusTicketInGestion()
    {
        $this->dataCall['datosSugarCRM']['type'] = 'seguimiento';

        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $this->assertNotNull($content->data->call_id);
        $this->assertNotNull($content->data->ticket_id);

        $ticket = Tickets::find($content->data->ticket_id);
        $this->assertEquals($ticket->estado, 4);
    }

    /** @test */
    public function postCallStatusTicketClosed()
    {
        $this->dataCall['datosSugarCRM']['type'] = 'seguimiento';
        $this->dataCall['datosSugarCRM']['ticket']['is_closed'] = true;
        $this->dataCall['datosSugarCRM']['ticket']['motivo_cierre'] = 'no_contesta';

        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $this->assertNotNull($content->data->call_id);
        $this->assertNotNull($content->data->ticket_id);

        $ticket = Tickets::find($content->data->ticket_id);
        $this->assertEquals($ticket->estado, 7);
        $this->assertEquals($ticket->proceso, 'no_contesta');
    }

    /** @test */
    public function postCallStatusTicketNoContesta()
    {
        $this->dataCall['datosSugarCRM']['type'] = 'seguimiento';
        $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);

        $this->dataCall['datosSugarCRM']['type'] = 'seguimiento';
        $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);

        $this->dataCall['datosSugarCRM']['type'] = 'seguimiento';
        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);

        $content = json_decode($response->content());

        $ticket = Tickets::find($content->data->ticket_id);
        $this->assertEquals($ticket->estado, 2);
    }

    /** @test */
    public function postCallSuccesfullWithMarcaModelo()
    {
        $this->dataCall['datosSugarCRM']['meeting']['marca'] = 1;
        $this->dataCall['datosSugarCRM']['meeting']['modelo'] = 2;

        $response = $this->json('POST', $this->baseUrl . 'calls', $this->dataCall);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $this->assertNotNull($content->data->call_id);
        $this->assertNotNull($content->data->ticket_id);
        $this->assertNotNull($content->data->prospeccion_id);
        $this->assertNotNull($content->data->meeting_id);

        $prospeccion = ProspeccionCstm::where('id_c', $content->data->prospeccion_id)->first();
        $this->assertEquals('ALFA ROMEO 147.0', $prospeccion->modelo_c);
    }

    public function relationalDataComplete($content, $contact)
    {
        $contact = Contacts::where('deleted', 0)
            ->join('contacts_cstm', 'contacts.id', '=', 'contacts_cstm.id_c')
            ->where('contacts_cstm.numero_identificacion_c', $this->dataCall['datosSugarCRM']['meeting']['client']['numero_identificacion'])
            ->get()->first();
        $callContact = CallsContacts::where('contact_id', $contact->id)
                        ->where('call_id', $content->data->call_id)
                        ->first();
        $this->assertNotNull($callContact->id);

        $callTicket = TicketsCalls::where('cbt_tickets_callscbt_tickets_ida', $this->dataCall['datosSugarCRM']['ticket']['id'])
            ->where('cbt_tickets_callscalls_idb', $content->data->call_id)
            ->first();
        $this->assertNotNull($callTicket->id);

        $callProspeccion = CallsProspeccion::where('cbp_prospeccion_callscbp_prospeccion_ida', $content->data->prospeccion_id)
            ->where('cbp_prospeccion_callscalls_idb', $content->data->call_id)
            ->first();

        $this->assertNotNull($callProspeccion->id);

        $prospeccionContact = ProspeccionContacts::where('cbp_prospeccion_contactscontacts_ida', $contact->id)
            ->where('cbp_prospeccion_contactscbp_prospeccion_idb', $content->data->prospeccion_id)
            ->first();
        $this->assertNotNull($prospeccionContact->id);

        $prospeccionMeeting = ProspeccionMeetings::where('cbp_prospeccion_meetingsmeetings_idb', $content->data->meeting_id)
            ->where('cbp_prospeccion_meetingscbp_prospeccion_ida', $content->data->prospeccion_id)
            ->first();

        $this->assertNotNull($prospeccionMeeting->id);

        $prospeccionTicket = TicketsProspeccion::where('cbp_prospeccion_cbt_tickets_1cbt_tickets_idb', $content->data->ticket_id)
            ->where('cbp_prospeccion_cbt_tickets_1cbp_prospeccion_ida', $content->data->prospeccion_id)
            ->first();

        $this->assertNotNull($prospeccionTicket->id);

        $meetingContact = MeetingsContacts::where('meeting_id', $content->data->meeting_id)
            ->where('contact_id', $contact->id)
            ->first();

        $this->assertNotNull($meetingContact->id);

        $meetingUser = UsersMeetings::where('meeting_id', $content->data->meeting_id)
            ->where('user_id', '10296f94-ebf3-42a8-a42d-5c880a18abca')
            ->first();
        $this->assertNotNull($meetingUser->id);
    }

    public function dataContactComplete($contact)
    {
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $contact->modified_user_id);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['names'], $contact->first_name);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['surnames'], $contact->last_name);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['cellphone_number'], $contact->phone_mobile);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['phone_home'], $contact->phone_home);
        $this->assertEquals('10296f94-ebf3-42a8-a42d-5c880a18abca', $contact->assigned_user_id);
        $this->assertEquals('1', $contact->team_id);
        $this->assertEquals('1', $contact->team_set_id);

        $contacts_cstm = ContactsCstm::where('id_c', $contact->id)->first();
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['tipo_identificacion'], $contacts_cstm->tipo_identificacion_c);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['gender'], $contacts_cstm->genero_c);
        $this->assertEquals('01', $contacts_cstm->tipo_cliente_c);
        $this->assertEquals('1', $contacts_cstm->tipo_contacto_c);
    }

    public function dataEmailComplete($contact)
    {
        $emailContacts = EmailAddreses::where('email_address', $this->dataCall['datosSugarCRM']['meeting']['client']['email'])->first();
        $this->assertNotNull($emailContacts->id);

        $beanRel = EmailAddrBeanRel::where('email_address_id', $emailContacts->id)->where('bean_id', $contact->id)->first();
        $this->assertNotNull($beanRel->id);
        $this->assertEquals('1', $beanRel->primary_address);
        $this->assertEquals('Contacts', $beanRel->bean_module);
    }

    public function datCallComplete($callId)
    {
        $call = Calls::find($callId);
        $this->assertEquals($this->dataTicket['datosSugarCRM']['nombres'] . ' '. $this->dataTicket['datosSugarCRM']['apellidos'], $call->name);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $call->modified_user_id);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $call->created_by);
        $this->assertEquals('0', $call->deleted);
        $this->assertEquals($this->dataCall['datosSugarCRM']['duration_hours'], $call->duration_hours);
        $this->assertEquals($this->dataCall['datosSugarCRM']['duration_minutes'], $call->duration_minutes);
        $this->assertEquals('2021-12-24 19:59:00', $call->date_start);
        $this->assertEquals('2021-12-24 20:09:00', $call->date_end);
        $this->assertEquals('cbt_Tickets', $call->parent_type);
        $this->assertEquals($this->dataCall['datosSugarCRM']['ticket']['id'], $call->parent_id);
        $this->assertEquals($this->dataCall['datosSugarCRM']['status'], $call->status);
        $this->assertEquals($this->dataCall['datosSugarCRM']['direction'], $call->direction);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $call->assigned_user_id);
        $this->assertEquals('1', $call->team_id);
        $this->assertEquals('1', $call->team_set_id);

        $callCSTM = CallsCstm::where('id_c', $callId)->first();
        $this->assertEquals($this->dataCall['datosSugarCRM']['category'], $callCSTM->categoria_llamada_c);
        $this->assertEquals($this->dataCall['datosSugarCRM']['type'], $callCSTM->tipo_llamada_c);
        $this->assertEquals('TK', $callCSTM->origen_creacion_c);
        $this->assertEquals('no', $callCSTM->llamada_automatica_c);
        $this->assertEquals('FREDDY MANUEL VARGAS JACOME - Cel:0987519726', $callCSTM->info_contacto_c);
    }

    public function dataProspeccionComplete($prospeccionId)
    {
        $autoincrement = Prospeccion::count();
        $prospeccion = Prospeccion::find($prospeccionId);
        $this->assertEquals( 'PROSPECTO-'.$autoincrement, $prospeccion->name);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $prospeccion->modified_user_id);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $prospeccion->created_by);
        $this->assertEquals('Prueba de Manejo: El cliente se acerca a la agencia...', $prospeccion->description);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['numero_identificacion'], $prospeccion->numero_identificacion);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['tipo_identificacion'], $prospeccion->tipo_identificacion);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['names'], $prospeccion->nombres);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['surnames'], $prospeccion->apellidos);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['cellphone_number'], $prospeccion->celular);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['phone_home'], $prospeccion->telefono);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['client']['email'], $prospeccion->email);
        $this->assertEquals($this->dataCall['datosSugarCRM']['campania'], $prospeccion->campaign_id_c);
        $this->assertEquals('2', $prospeccion->fuente);
        $this->assertEquals('5', $prospeccion->estado);
        $this->assertEquals('1', $prospeccion->team_id);
        $this->assertEquals('1', $prospeccion->team_set_id);
        $this->assertEquals('1', $prospeccion->brinda_identificacion);
        $this->assertEquals('1', $prospeccion->brinda_identificacion);
        $this->assertEquals('10296f94-ebf3-42a8-a42d-5c880a18abca', $prospeccion->assigned_user_id);
        $this->assertEquals('d8365338-9206-11e9-a7c3-000c297d72b1', $prospeccion->cb_lineanegocio_id_c);

        $prospeccionCSTM = ProspeccionCstm::where('id_c', $prospeccion->id)->first();
        $this->assertEquals('10296f94-ebf3-42a8-a42d-5c880a18abca', $prospeccionCSTM->user_id_c);
        $this->assertEquals($this->dataTicket['datosSugarCRM']['medio'], $prospeccionCSTM->medio_c);
    }

    public function dataMeetingComplete($meetId)
    {
        $meeting = Meetings::find($meetId);
        $this->assertEquals( 'BC Manuelito Torres', $meeting->name);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $meeting->modified_user_id);
        $this->assertEquals('2fa28a3f-9a39-3d63-4729-5b7353ef1fd9', $meeting->created_by);
        $this->assertEquals('Prueba de Manejo: El cliente se acerca a la agencia...', $meeting->description);
        $this->assertEquals('0', $meeting->deleted);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['location'], $meeting->location);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['duration_hours'], $meeting->duration_hours);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['duration_minutes'], $meeting->duration_minutes);

        $this->assertEquals('2021-03-31 09:59:00', $meeting->date_start);
        $this->assertEquals('2021-03-31 10:01:00', $meeting->date_end);
        $this->assertEquals('TK', $meeting->parent_type);
        $this->assertEquals($this->dataCall['datosSugarCRM']['ticket']['id'], $meeting->parent_id);
        $this->assertEquals('Planned', $meeting->status);
        $this->assertEquals('10296f94-ebf3-42a8-a42d-5c880a18abca', $meeting->assigned_user_id);
        $this->assertEquals('1', $meeting->team_id);
        $this->assertEquals('1', $meeting->team_set_id);

        $meetingCSTM = MeetingsCstm::where('id_c', $meeting->id)->first();
        $this->assertEquals('Manuelito Torres Cel: 0987512224', $meetingCSTM->info_contacto_c);
        $this->assertEquals('TK', $meetingCSTM->origen_creacion_c);
        $this->assertEquals('2', $meetingCSTM->tipo_c);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['visit_type'], $meetingCSTM->visita_tipo_c);
        $this->assertEquals($this->dataCall['datosSugarCRM']['meeting']['type'], $meetingCSTM->tipo_cita_c);
    }
}
