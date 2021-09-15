<?php

namespace Tests\Feature;

use App\Models\Avaluos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetAvaluoTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    public $dataAvaluo = [];

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUserSanctum();

        $this->dataAvaluo = [
            "id" => null,
            "contact" => "0015ad44-0a08-11ea-b67c-5883aaf14456",
            "document" => "1722898838",
            "coordinator" => "b9187d88-6ee4-c794-27f5-552bb40ee0d4",
            "plate" => "PCR5214",
            "brand" =>  ["id" => 1,"name" => "Chevrolet","status" => true],
            "model" => ["id" => 1,"name" => "Aveo","status" => true],
            "color" => ["id" => 1,"name" => "Blanco","status" => true],
            "year" => "2021",
            "mileage" => "23.412",
            "unity" => "km",
            "status" => "1",
            "comment" => "qweasd",
            "observation" => "qwe", //requerido si  son diferente priceNew y priceNewEdit o priceFinal y priceFinalEdit
            "priceNew" => null,
            "priceNewEdit" => "234",
            "priceFinal" => null,
            "priceFinalEdit" => "123"
        ];
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_should_get_avaluo()
    {
        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $contentCreateAvaluo = json_decode($response->content());

        $response = $this->json('GET', $this->baseUrl . 'getAvaluo', ['id' => $contentCreateAvaluo->data->avaluo_id]);
        $contentGetAvaluo = json_decode($response->content());

        $avaluo = Avaluos::find($contentCreateAvaluo->data->avaluo_id);

        $this->assertEquals($contentGetAvaluo->avaluo->description, $avaluo->description);
        $this->assertEquals($contentGetAvaluo->avaluo->placa, $avaluo->placa);
        $this->assertEquals($contentGetAvaluo->avaluo->marca, $avaluo->marca);
        $this->assertEquals($contentGetAvaluo->avaluo->modelo, $avaluo->modelo);
        $this->assertEquals($contentGetAvaluo->avaluo->color, $avaluo->color);
        $this->assertEquals($contentGetAvaluo->avaluo->recorrido, $avaluo->recorrido);
        $this->assertEquals($contentGetAvaluo->avaluo->tipo_recorrido, $avaluo->tipo_recorrido);
        $this->assertEquals($contentGetAvaluo->avaluo->name, $avaluo->name);
        $this->assertEquals($contentGetAvaluo->avaluo->description, $avaluo->description);
        $this->assertEquals($contentGetAvaluo->avaluo->assigned_user_id, $avaluo->assigned_user_id);
        $this->assertEquals($contentGetAvaluo->avaluo->precio_final, $avaluo->precio_final);
        $this->assertEquals($contentGetAvaluo->avaluo->precio_nuevo, $avaluo->precio_nuevo);
        $this->assertEquals($contentGetAvaluo->avaluo->precio_nuevo_mod, $avaluo->precio_nuevo_mod);
        $this->assertEquals($contentGetAvaluo->avaluo->precio_final_mod, $avaluo->precio_final_mod);
        $this->assertEquals($contentGetAvaluo->avaluo->estado_avaluo, $avaluo->estado_avaluo);
        $this->assertEquals($contentGetAvaluo->avaluo->observacion, $avaluo->observacion);
        $this->assertEquals($contentGetAvaluo->avaluo->comentario, $avaluo->comentario);
        $this->assertEquals($contentGetAvaluo->avaluo->deleted, 0);
    }

    public function test_should_not_get_avaluo()
    {
        $response = $this->json('GET', $this->baseUrl . 'getAvaluo', ['id' => 'notExists']);
        $contentGetAvaluo = json_decode($response->content());
        $this->assertEquals($contentGetAvaluo->error, 'Avaluo not found');
    }
}