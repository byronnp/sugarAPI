<?php
namespace App\Services;

use App\Models\Tickets;
use App\Models\TicketsCstm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TicketInconcertClass {
  public $numero_identificacion;
  public $contentUrl;
  public $thankyouPageUrl;
  public $tipo_identificacion;
  public $email;
  public $firstname;
  public $lastname;
  public $tipo_transaccion;
  public $linea_negocio;
  public $tickeId;
  public $ticketName;
  public $ticketInteraction;
  public $mobile;

  public function create($extraFields)
  {
    $data = [
      "serviceToken" => env('inconcertTokenTicket'),
      "serviceAction" => "c2c",
      "contentUrl" => $this->contentUrl,
      "thankyouPageUrl" => $this->thankyouPageUrl,
      "contactData" => [
        "numero_identificacion" => $this->numero_identificacion,
        "tipo_identificacion" => $this->tipo_identificacion,
        "email" => $this->email,
        "firstname" => $this->firstname,
        "lastname" => $this->lastname,
        "tipo_transaccion" => getTipoTransaccion($this->tipo_transaccion),
        "linea_negocio" => getLineaNegocio($this->linea_negocio),
        "TicketId" => $this->tickeId,
        "TicketName" => $this->ticketName,
        "TicketInteraction" => $this->ticketInteraction,
        "language" => "es",
        "mobile" => intval($this->mobile),
        "phone" => intval($this->mobile)
      ]
    ];

    $data["contactData"] = array_merge($data["contactData"], $extraFields);

    $response = Http::post(env('inconcertWS'), $data);

    return $response->json();
  }
}
