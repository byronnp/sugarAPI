<?php

namespace Tests\Feature;

use App\Models\Companies;
use App\Models\Contacts;
use App\Models\ContactsCstm;
use App\Models\Prospeccion;
use App\Models\User;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicesTest extends TestCase
{
  use RefreshDatabase,WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
    Companies::factory()->create();
    Sanctum::actingAs(
      User::factory()->create([]),
      ['environment: dev']
    );
    $userLogin = Users::where('user_name','admin')->first();
Session::put('user',$userLogin);//Simular inicio de sesion sugar
  }

  public function testGetDocumentConSugar()
  {
    $faker = [
      'first_name' => 'CRISTIANO MESSI',
      'last_name' => 'RONALDO PULGA',
      'phone_mobile' => '0984434641',
      'phone_home' => '022408354',
    ];

    $faker = Contacts::create($faker);
    $fakerContacts = [
      'id_c' => $faker->id,
      'numero_identificacion_c' => '1722898838',
      'genero_c' => 'M',
      'tipo_contacto_c' => 2,
      'sospechoso_c' => 1,
      'sospechoso_text_c'=> 'SOSPECHOSO DE FRAUDE'
    ];
    $fakerContacts = ContactsCstm::create($fakerContacts);
    $response = $this->getJson('services/getDocument?document='.$fakerContacts['numero_identificacion_c'].'&type=C');
    $dataResponse = json_decode($response->content());
    Contacts::where('id',$faker->id)->delete();
    ContactsCstm::where('numero_identificacion_c',$fakerContacts['numero_identificacion_c'])->delete();
    $this->assertEquals($dataResponse->tipoIdentificacion, 'C');
    $this->assertEquals($dataResponse->numeroIdentificacion, $fakerContacts['numero_identificacion_c']);
    $this->assertEquals($dataResponse->firstName, 'CRISTIANO MESSI');
    $this->assertEquals($dataResponse->lastName, 'RONALDO PULGA');
    $this->assertEquals($dataResponse->genero, 'M');
    $this->assertEquals($dataResponse->phoneHome, '022408354');
    $this->assertEquals($dataResponse->phoneMobile, '0984434641');
    $this->assertEquals($dataResponse->sospechoso, '1');
    $this->assertEquals($dataResponse->sospechosoText, 'SOSPECHOSO DE FRAUDE');
    $this->assertEquals($dataResponse->tipoContacto, '2');
    $this->assertEquals($dataResponse->tipoRuc, '01');
    $this->assertEquals($dataResponse->valid, true);
    $this->assertEquals($dataResponse->error, null);
    $this->assertEquals($dataResponse->codigoError, null);
  }

  public function testGetDocumentCedulaConS3s ()
  {
    Http::fake([
      env('S3S') . '/casabacaWebservices/processDatabookRestImpl/databookConsultarDatos?compania=01&tipoConsulta=TIT&tipoIdentificacion=C&identificacion=1727348375' => Http::response([
        "tipoIdentificacion" => "C",
        "numeroIdentificacion" => "1727348375",
        "nombres" => "SHEYLA SAMANTHA",
        "apellidos" => "MOSQUERA MALDONADO",
        "genero" => "F",
        "error" => "OK",
        "codigoError" => "00",
      ], 200)
    ]);
    prospeccion::where('numero_identificacion','1727348375')->delete();
    $response = $this->getJson('services/getDocument?document=1727348375&type=C');
    $dataResponse = json_decode($response->content());
    $this->assertEquals($dataResponse->tipoIdentificacion, 'C');
    $this->assertEquals($dataResponse->numeroIdentificacion, '1727348375');
    $this->assertEquals($dataResponse->firstName, 'SHEYLA SAMANTHA');
    $this->assertEquals($dataResponse->lastName, 'MOSQUERA MALDONADO');
    $this->assertEquals($dataResponse->genero, 'F');
    $this->assertEquals($dataResponse->phoneHome, null);
    $this->assertEquals($dataResponse->phoneMobile, null);
    $this->assertEquals($dataResponse->email, null);
    $this->assertEquals($dataResponse->sospechoso, 0);
    $this->assertEquals($dataResponse->sospechosoText, null);
    $this->assertEquals($dataResponse->tipoContacto, null);
    $this->assertEquals($dataResponse->tipoRuc, '01');
    $this->assertEquals($dataResponse->valid, true);
    $this->assertEquals($dataResponse->error, 'OK');
    $this->assertEquals($dataResponse->codigoError, '00');
  }

  public function testGetDocumentRucNuevoS3s ()
  {
    Http::fake([
      env('S3S') . '/casabacaWebservices/processDatabookRestImpl/databookConsultarDatos?compania=01&tipoConsulta=TIT&tipoIdentificacion=R&identificacion=1722898838001' => Http::response([
        "error" => "NO existe datos para el ruc consultado",
        "codigoError" => "98"
      ], 200)
    ]);
    prospeccion::where('numero_identificacion','1722898838001')->delete();
    $response = $this->getJson('services/getDocument?document=1722898838001&type=R');
    $dataResponse = json_decode($response->content());
    $this->assertEquals($dataResponse->valid, true);
    $this->assertEquals($dataResponse->error, 'NO existe datos para el ruc consultado');
    $this->assertEquals($dataResponse->codigoError, 98);
  }


  public function testGetDocumentCedulaNoValidaS3s ()
  {
    Http::fake([
      env('S3S') . '/casabacaWebservices/processDatabookRestImpl/databookConsultarDatos?compania=01&tipoConsulta=TIT&tipoIdentificacion=C&identificacion=1722898839' => Http::response([
        "error" => "Identificacion no es valida",
        "codigoError" => "01"
      ], 200)
    ]);
    prospeccion::where('numero_identificacion','1722898839')->delete();
    $response = $this->getJson('services/getDocument?document=1722898839&type=C');
    $dataResponse = json_decode($response->content());
    $this->assertEquals($dataResponse->valid, false);
    $this->assertEquals($dataResponse->error, 'La cédula o RUC ingresado no es válido');
    $this->assertEquals($dataResponse->codigoError, 01);
  }

  public function testGetDocumentRequeridoDocument ()
  {
    $response = $this->getJson('services/getDocument?&type=C');
    $content = json_decode($response->content());
    $this->assertEquals($content->errors->document[0], 'El campo document es requerido.');
  }

  public function testGetDocumentRequeridoType ()
  {
    $response = $this->getJson('services/getDocument?document=1722898838');
    $content = json_decode($response->content());
    $this->assertEquals($content->errors->type[0], 'El campo type es requerido.');
  }

  public function testGetDocumentOpcionNoValidaType ()
  {
    $response = $this->getJson('services/getDocument?document=1722898838&type=H');
    $content = json_decode($response->content());
    $this->assertEquals($content->errors->type[0], 'El campo type seleccionado no es válido.');
  }

}
