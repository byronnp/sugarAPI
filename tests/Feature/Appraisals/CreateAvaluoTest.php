<?php

namespace Tests\Feature;

use App\Models\Avaluos;
use App\Models\CheckList;
use App\Models\CheckListAvaluo;
use App\Models\Imagenes;
use App\Models\Imagenes_Avaluo;
use App\Models\TrafficAvaluos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateAvaluoTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    public $dataAvaluo = [];

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setInitDataUserSanctum();
        Storage::fake('avatars');

        $image = UploadedFile::fake()->image('testPicture.jpeg', 100, 100);

        $this->dataAvaluo = [
            "id" => null,
            "traffic" => createdID(),
            'testPicture1' => $image,
            'extraPicture' => [$image, $image],
            'extraPicture[1]' => $image,
            "contact" => "0015ad44-0a08-11ea-b67c-5883aaf14456",
            "document" => "1722898838",
            "coordinator" => "b9187d88-6ee4-c794-27f5-552bb40ee0d4",
            "plate" => "PCR5214",
            "brand" =>  '{"id":4,"name":"FIAT","status":true}',
            "model" => '{"id":227,"name":"Argo","status":true,"brand":{"id":4,"name":"FIAT","status":true},"start_year":"2021","end_year":"2022"}',
            "color" => '{"id":1,"name":"Blanco","status":true}',
            "year" => "2022",
            "mileage" => "23412",
            "unity" => "km",
            "status" => "1",
            "comment" => "qweasd",
            "observation" => "qweasd",
            "description" => '{"id":897,"description":"Argo Trekking 1.3L Firefly SUV 4x2 T/M A/A 100hp/6000rpm 134Nm/3500rpm 2AB ABS+EBD ESC HAC TPMS aros 15\" cam retro pant 7\" Uconnect BRA (2022)","start_year":2021,"end_year":2022,"status":true,"model":{"id":227,"name":"Argo","status":true,"brand":4,"start_year":"2021","end_year":"2022"}}',
            "priceNew" => null,
            "priceNewEdit" => "234",
            "priceFinal" => null,
            "priceFinalEdit" => "123",
            "pics" => '[{"name":"testPicture1","multiple":false},{"name":"testPicture2","multiple":false},{"name":"extraPicture","multiple":true}]',
            "checklist" => '[{"id":1,"description":"Tren Motriz","status":true,"option":"A","observation":"observacion 1","cost":"1000"},{"id":2,"description":"Dirección","status":true,"option":"E","observation":"observación 2","cost":"2000"},{"id":3,"description":"Suspensión","status":true,"option":"NA"}]'
        ];
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    /*public function test_should_update_avaluo()
    {
        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());
        dd($content);
        $response->assertStatus(200);

        $this->assertNotNull($content->data->avaluo_id);
        $this->assertNotNull($content->data->avaluo_name);

        $avaluo = Avaluos::find($this->dataAvaluo["id"]);

        $this->assertEquals($this->dataAvaluo["id"], $avaluo->id);
        $this->assertEquals($this->dataAvaluo["plate"], $avaluo->placa);
        $this->assertEquals($this->dataAvaluo["model"]["id"], $avaluo->modelo);
        $this->assertEquals($this->dataAvaluo["brand"]["id"], $avaluo->marca);
        $this->assertEquals($this->dataAvaluo["color"]["id"], $avaluo->color);
        $this->assertEquals($this->dataAvaluo["mileage"], $avaluo->recorrido);
        $this->assertEquals($this->dataAvaluo["unity"], $avaluo->tipo_recorrido);
        $this->assertEquals($this->dataAvaluo["status"], $avaluo->estado_avaluo);
        $this->assertEquals($this->dataAvaluo["comment"], $avaluo->comentario);
        $this->assertEquals($this->dataAvaluo["observation"], $avaluo->observacion);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->created_by);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->modified_user_id);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->assigned_user_id);
        $this->assertEquals($this->dataAvaluo["contact"], $avaluo->contact_id_c);
        $this->assertEquals(0, $avaluo->deleted);
    }*/

    public function test_should_create_avaluo()
    {
        Http::fake([
            env('STRAPI_URL'. '/appraisal-images') => Http::response([
                'id' => 15,
                'name' => 'nameFakePicture',
                'images' => [
                    [
                        "formats" => [
                        "thumbnail" => ["url" => 'urlTestStrapi']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $this->dataAvaluo["id"] = null;

        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertNotNull($content->data->avaluo_id);
        $this->assertNotNull($content->data->avaluo_name);

        $avaluo = Avaluos::find($content->data->avaluo_id);

        $this->assertEquals($this->dataAvaluo["plate"], $avaluo->placa);
        $this->assertEquals('227', $avaluo->modelo);
        $this->assertEquals('4', $avaluo->marca);
        $this->assertEquals('1', $avaluo->color);
        $this->assertEquals($this->dataAvaluo["mileage"], $avaluo->recorrido);
        $this->assertEquals($this->dataAvaluo["unity"], $avaluo->tipo_recorrido);
        $this->assertEquals($this->dataAvaluo["status"], $avaluo->estado_avaluo);
        $this->assertEquals($this->dataAvaluo["comment"], $avaluo->comentario);
        $this->assertEquals($this->dataAvaluo["observation"], $avaluo->observacion);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->created_by);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->modified_user_id);
        $this->assertEquals($this->dataAvaluo["coordinator"], $avaluo->assigned_user_id);
        $this->assertEquals($this->dataAvaluo["contact"], $avaluo->contact_id_c);
        $this->assertEquals(0, $avaluo->deleted);
    }

    public function test_should_create_images_avaluo()
    {
        Http::fake([
            env('STRAPI_URL'. '/appraisal-images') => Http::response([
                'id' => 15,
                'name' => 'nameFakePicture',
                'images' => [
                    [
                        "formats" => [
                            "thumbnail" => ["url" => '/urlTestStrapi']
                        ]
                    ],
                    [
                        "formats" => [
                            "thumbnail" => ["url" => '/urlTestStrapiExtra']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $this->dataAvaluo["id"] = null;

        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertNotNull($content->data->avaluo_id);
        $this->assertNotNull($content->data->avaluo_name);

        $imagesAppraisal = Imagenes_Avaluo::where('cba_imagenavaluo_cba_avaluoscba_avaluos_ida', $content->data->avaluo_id)->pluck('cba_imagenavaluo_cba_avaluoscba_imagenavaluo_idb');
        $this->assertEquals(3, count($imagesAppraisal));

        $imageTestPicture1 = Imagenes::whereIn('id', $imagesAppraisal)
            ->where('imagen_path', env('STRAPI_URL'). '/urlTestStrapi')
            ->where('orientacion', 'nameFakePicture')
            ->where('name', '15')
            ->count();
        $this->assertEquals(1, $imageTestPicture1);

        $imageExtraPicture1 = Imagenes::whereIn('id', $imagesAppraisal)
            ->where('imagen_path', env('STRAPI_URL'). '/urlTestStrapi')
            ->where('orientacion', 'nameFakePicture0')
            ->where('name', '15')
            ->count();
        $this->assertEquals(1, $imageExtraPicture1);

        $imageExtraPicture2 = Imagenes::whereIn('id', $imagesAppraisal)
            ->where('imagen_path', env('STRAPI_URL'). '/urlTestStrapiExtra')
            ->where('orientacion', 'nameFakePicture1')
            ->where('name', '15')
            ->count();
        $this->assertEquals(1, $imageExtraPicture2);

    }

    public function test_should_create_checkList()
    {
        Http::fake([
            env('STRAPI_URL'. '/appraisal-images') => Http::response([
                'id' => 15,
                'name' => 'nameFakePicture',
                'images' => [
                    [
                        "formats" => [
                            "thumbnail" => ["url" => 'urlTestStrapi']
                        ]
                    ],
                    [
                        "formats" => [
                            "thumbnail" => ["url" => 'urlTestStrapiExtra']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $this->dataAvaluo["id"] = null;

        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertNotNull($content->data->avaluo_id);
        $this->assertNotNull($content->data->avaluo_name);

        $checkListAppraisal = CheckListAvaluo::where('cba_checklist_avaluo_cba_avaluoscba_avaluos_ida', $content->data->avaluo_id)->pluck('cba_checklist_avaluo_cba_avaluoscba_checklist_avaluo_idb');
        $this->assertEquals(3, count($checkListAppraisal));

        $checkList1 = CheckList::whereIn('id', $checkListAppraisal)
            ->where('item_id', '1')
            ->where('deleted', '0')
            ->first();
        $this->assertEquals('Tren Motriz', $checkList1->item_description);
        $this->assertEquals('observacion 1', $checkList1->description);
        $this->assertEquals('A', $checkList1->estado);
        $this->assertEquals(1000, $checkList1->costo);

        $checkList2 = CheckList::whereIn('id', $checkListAppraisal)
            ->where('item_id', '2')
            ->where('deleted', '0')
            ->first();
        $this->assertEquals('Dirección', $checkList2->item_description);
        $this->assertEquals('observación 2', $checkList2->description);
        $this->assertEquals('E', $checkList2->estado);
        $this->assertEquals(2000, $checkList2->costo);

        $checkList3 = CheckList::whereIn('id', $checkListAppraisal)
            ->where('item_id', '3')
            ->where('deleted', '0')
            ->first();
        $this->assertEquals('Suspensión', $checkList3->item_description);
        $this->assertNull($checkList3->description);
        $this->assertEquals(0, $checkList3->costo);
        $this->assertEquals('NA', $checkList3->estado);
    }

    public function test_should_validate_data()
    {
        $this->dataAvaluo = [];
        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(422);
        $this->assertEquals('Documento es campo requerido', $content->errors->document[0]);
        $this->assertEquals('Placa es campo requerido', $content->errors->plate[0]);
        $this->assertEquals('Marca es campo requerido', $content->errors->brand[0]);
        $this->assertEquals('Modelo es campo requerido', $content->errors->model[0]);
        $this->assertEquals('Color es campo requerido', $content->errors->color[0]);
        $this->assertEquals('Recorrido es campo requerido', $content->errors->mileage[0]);
        $this->assertEquals('Tipo de recorrido es campo requerido', $content->errors->unity[0]);
        $this->assertEquals('Estado es requerido', $content->errors->status[0]);
        $this->assertEquals('Coordinador es requerido', $content->errors->coordinator[0]);
        $this->assertEquals('Contacto es requerido', $content->errors->contact[0]);
    }

    public function test_should_create_relationship_traffic()
    {
        Http::fake([
            env('STRAPI_URL'. '/appraisal-images') => Http::response([
                'id' => 15,
                'name' => 'nameFakePicture',
                'images' => [
                    [
                        "formats" => [
                            "thumbnail" => ["url" => 'urlTestStrapi']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $traffic = TrafficAvaluos::where('cba_avaluos_cb_traficocontrolcba_avaluos_idb', $content->data->avaluo_id)
            ->where('cba_avaluos_cb_traficocontrolcb_traficocontrol_ida',$this->dataAvaluo["traffic"])
            ->where('deleted', '0')->first();
        $this->assertNotNull($traffic->id);
    }

    public function test_should_validate_external_data()
    {
        $this->dataAvaluo = [
            "contact" => 'notExist',
            "coordinator" => 'notExist'
            ];

        $response = $this->json('POST', $this->baseUrl . 'createUpdateAvaluo', $this->dataAvaluo);
        $content = json_decode($response->content());

        $response->assertStatus(422);
        $this->assertEquals('Coordinador inválido en Sugar', $content->errors->coordinator[0]);
        $this->assertEquals('Contacto es inválido en Sugar', $content->errors->contact[0]);
    }
}
