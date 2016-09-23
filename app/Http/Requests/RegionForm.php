<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegionForm extends Request
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
            "reg_nombre"	=> "required|min:5|max:50"
        ];
    }
    public function messages()
    {
    	return [
    			'reg_nombre.required' => 'El campo nombre es requerido!',
    			'reg_nombre.min' => 'El campo nombre no puede tener menos de 5 carácteres',
    			'reg_nombre.max' => 'El campo nombre no puede tener más de 50 carácteres',
    	];
    }
}
