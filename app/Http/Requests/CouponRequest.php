<?php

namespace App\Http\Requests;

use App\Rules\ValidateCampaing;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idcampana' => ['required','exists:App\Models\Coupons\Campaigns,id', new ValidateCampaing()],
            'cedula' => ['required'],
            'nombres' => ['required'],
            'apellidos' => ['required'],
            'email' => ['required'],
            'celular' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'idcampana.required' => 'Id campaña es requerido',
            'idcampana.exists' => 'Id campaña no existe',
            'cedula.required' => 'Cédula es requerido',
            'nombres.required' => 'Nombres es requerido',
            'apellidos.required' => 'Apellidos es requerido',
            'email.required' => 'Correo Electrónico es requerido',
            'celular.required' => 'Celular es requerido',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
