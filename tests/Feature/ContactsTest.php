<?php

namespace Tests\Feature;

use App\Models\Contacts;
use App\Models\EmailAddrBeanRel;
use App\Models\EmailAddreses;
use App\Models\Nationality;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUser();
    }

    /** @test */
    public function getContactExists()
    {
        $response = $this->get('/client/1002234944');
        $content = json_decode($response->content());
        $contact = Contacts::where('deleted', 0)
            ->join('contacts_cstm', 'contacts.id', '=', 'contacts_cstm.id_c')
            ->where('contacts_cstm.numero_identificacion_c', '1002234944')
            ->select('contacts.id', 'contacts.first_name', 'contacts.last_name', 'contacts_cstm.tipo_identificacion_c', 'contacts_cstm.numero_identificacion_c', 'contacts.birthdate', 'contacts.phone_home', 'contacts.phone_mobile', 'contacts_cstm.estado_civil_c')
            ->first();
        if($contact->nacionalidad_c) {
            $nacionality = Nationality::find($contact->nacionalidad_c);
            $contact->nacionality = $nacionality->nombre;
        }

        $emails = EmailAddrBeanRel::where('bean_id', $contact->id)
            ->where('primary_address', 1)
            ->where('deleted', 0)->pluck('email_address_id');
        $contact->email = EmailAddreses::whereIn('id', $emails)->where('deleted', 0)->select('email_address')->first();

        $this->assertEquals(json_decode($contact), $content->contact);
        $response->assertStatus(200);
    }

    /** @test */
    public function getContactTicketExists()
    {
      $response = $this->get('/client/58abf3bc-4058-20eb-9cfe-5fa99b8ba80c');
      $content = json_decode($response->content());
      $ticket = Tickets::find('58abf3bc-4058-20eb-9cfe-5fa99b8ba80c');
      $contact = Contacts::where('deleted', 0)
        ->join('contacts_cstm', 'contacts.id', '=', 'contacts_cstm.id_c')
        ->where('contacts_cstm.numero_identificacion_c', $ticket->numero_identificacion)
        ->select('contacts.id', 'contacts.first_name', 'contacts.last_name', 'contacts_cstm.tipo_identificacion_c', 'contacts_cstm.numero_identificacion_c', 'contacts.birthdate', 'contacts.phone_home', 'contacts.phone_mobile', 'contacts_cstm.estado_civil_c')
        ->first();
      if($contact->nacionalidad_c) {
        $nacionality = Nationality::find($contact->nacionalidad_c);
        $contact->nacionality = $nacionality->nombre;
      }

      $emails = EmailAddrBeanRel::where('bean_id', $contact->id)
        ->where('primary_address', 1)
        ->where('deleted', 0)->pluck('email_address_id');
      $contact->email = EmailAddreses::whereIn('id', $emails)->where('deleted', 0)->select('email_address')->first();

      $this->assertJson($contact, json_encode($content->contact));
      $response->assertStatus(200);
    }

    /** @test */
    public function getContactNotExists()
    {
        $response = $this->get('/client/001002234944001');
        $content = json_decode($response->content());
        $this->assertNull($content->contact);
        $response->assertStatus(200);
    }
}
