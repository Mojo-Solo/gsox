<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorsRequest extends FormRequest
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
            'company_name' => 'required',
            'contact_name' => 'required',
            'phone_number' => 'required',
            'country_id' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'address1' => 'required',
        ];
    }
}
