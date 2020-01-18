<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use Illuminate\Http\Request;
use Validator;

class Register
{

    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function validate()
    {
        return Validator::make($this->request->all(), $this->rules(), $this->messages());
    }

    private function rules()
    {
        return [
            'first' => 'required|min:2|max:255',
            'last' => 'required|min:2|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                'regex:/^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$/'
            ],
            'g-recaptcha-response' => ['required', new ReCaptcha()]
        ];
    }

    private function messages()
    {
        return [
            'g-recaptcha-response.required' => 'ReCaptcha is required',
            'password.regex' => 'Password must be at least 8 characters long, contain one upper case letter, one lower case letter and (one number OR one special character). May NOT contain spaces'
        ];
    }
}
