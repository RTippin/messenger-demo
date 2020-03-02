<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginLoggerService;
use App\Services\Messenger\MessengerLocationService;
use App\Services\RegisterService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Exception;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * @var RegisterService
     */
    protected $registerService;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var LoginLoggerService
     */
    protected $loggerService;
    /**
     * @var MessengerLocationService
     */
    protected $messengerLocationService;

    /**
     * RegisterController constructor.
     * @param Request $request
     * @param RegisterService $registerService
     * @param LoginLoggerService $loggerService
     * @param MessengerLocationService $messengerLocationService
     */
    public function __construct(Request $request,
                                RegisterService $registerService,
                                LoginLoggerService $loggerService,
                                MessengerLocationService $messengerLocationService
    )
    {
        $this->middleware(['guest', 'Registration']);
        $this->registerService = $registerService;
        $this->request = $request;
        $this->loggerService = $loggerService;
        $this->messengerLocationService = $messengerLocationService;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        $dispatch = $this->registerService->registerPost();
        if(!$dispatch['state']){
            return response()->json(['errors' => ['forms' => $dispatch['error']] , 'registered' => false], 400);
        }
        try{
            set_messenger_profile(auth()->user());
            $this->loggerService->store(messenger_profile());
            $this->messengerLocationService->update();
        }catch (Exception $e){
            report($e);
        }
        return response()->json(['registered' => true]);
    }
}
