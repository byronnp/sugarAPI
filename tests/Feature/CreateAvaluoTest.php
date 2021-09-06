<?php

namespace Tests\Feature;

use App\Models\Avaluos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateAvaluoTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    public $dataAvaluo = [];

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUserSanctum();

        $this->dataAvaluo = [
            "description" => "description Avaluo",
            "placa" => "PDA-14003",
            "marca" => "1",
            "modelo" => "10",
            "color" => "1",
            "recorrido" => "10000",
            "tipo_recorrido" => "km", //mi
            "estado_avaluo" => "N",
            "comentario" => "COMENTARIO",
            "observacion" => "OBSERVACION",
            "coordinador" => "b9187d88-6ee4-c794-27f5-552bb40ee0d4", //users
            "contacto" => "0015ad44-0a08-11ea-b67c-5883aaf14456", //contactos
        ];
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_should_create_avaluo()
    {
        $response = $this->json('POST', $this->baseUrl . 'avaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertNotNull($content->data->avaluo_id);
        $this->assertNotNull($content->data->avaluo_name);

        $avaluo = Avaluos::find($content->data->avaluo_id);
        $this->assertEquals($this->dataAvaluo["description"], $avaluo->description);
        $this->assertEquals($this->dataAvaluo["placa"], $avaluo->placa);
        $this->assertEquals($this->dataAvaluo["placa"], $avaluo->placa);
    }

    public function test_should_validate_data()
    {
        $this->dataAvaluo = [];
        $response = $this->json('POST', $this->baseUrl . 'avaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(422);
        $this->assertEquals('Placa es campo requerido', $content->errors->placa[0]);
        $this->assertEquals('Marca es campo requerido', $content->errors->marca[0]);
        $this->assertEquals('Modelo es campo requerido', $content->errors->modelo[0]);
        $this->assertEquals('Recorrido es campo requerido', $content->errors->recorrido[0]);
        $this->assertEquals('Tipo de recorrido es campo requerido', $content->errors->tipo_recorrido[0]);
        $this->assertEquals('Estado es requerido', $content->errors->estado_avaluo[0]);
        $this->assertEquals('Coordinador es requerido', $content->errors->coordinador[0]);
        $this->assertEquals('Contacto es requerido', $content->errors->contacto[0]);

    }
}
