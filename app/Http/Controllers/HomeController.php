<?php
namespace App\Http\Controllers;

use App\Mail\ContactUs;
use App\Rules\ReCaptcha;
use App\User;
use Illuminate\Http\Request;
use Validator;
use Mail;
use View;
use Cache;

class HomeController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function splash()
    {
        return view('splash');
    }

    public function availableAccounts()
    {
        $users = User::query()->oldest()->whereNotIn('email', ['admin@test.com', 'admin2@test.com', 'admin3@test.com'])->limit(10)->get()->shuffle()->reject(function ($user){
            return Cache::has(get_messenger_alias($user).'_online_'.$user->id) || Cache::has(get_messenger_alias($user).'_away_'.$user->id);
        });
        return response()->json([
            'html' => View::make('auth.partials.accounts')->with('users', $users)->render()
        ]);
    }

    public function contactUs()
    {
        return view('contact');
    }

    public function contactSend()
    {
        $messages = [
            'your_name.required' => 'Your name is required.',
            'your_name.min' => 'Your name must be at least 3 characters.',
            'your_email.required' => 'Your email is required.',
            'your_message.required' => 'Your message is required.',
            'your_message.min' => 'Your message must be at least 50 characters in length.',
            'g-recaptcha-response.required' => 'ReCaptcha is required'
        ];
        $validator = Validator::make($this->request->all(), [
            'your_name' => ['required','min:3'],
            'your_email' => ['required','email'],
            'your_message' => ['required', 'min:50' ],
            'g-recaptcha-response' => ['required', new ReCaptcha()]
        ],  $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => ['forms' => $validator->errors()]], 400);
        }
        $data = array("name" => $this->request->input('your_name'), "reply_email" => $this->request->input('your_email'), "the_message" => $this->request->input('your_message'));
        Mail::to("admin@test.com")->send(new ContactUs($data));
        return response()->json(['success' => 1, 'msg' => 'Thank you for contacting us '.$this->request->input('your_name').'. We will reply using the email you provided ('.$this->request->input('your_email').'). Please allow one business day for us to respond!'], 200);
    }

}
