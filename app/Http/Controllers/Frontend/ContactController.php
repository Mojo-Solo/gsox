<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\Frontend\Contact\SendContact;
use App\Http\Requests\Frontend\Contact\SendContactRequest;
use Illuminate\Support\Facades\Session;
/**
 * Class ContactController.
 */
class ContactController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if(session()->has('display_type')){
            if(session('display_type') == 'rtl'){
                $path = 'frontend-rtl';
            }else{
                $path = 'frontend';
            }
        }else if(config('app.display_type') == 'rtl'){
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        abort(404);
        return view($this->path.'.contact');
    }

    /**
     * @param SendContactRequest $request
     *
     * @return mixed
     */
     private function get_client_ip()
     {
          $ipaddress = '';
          if (getenv('HTTP_CLIENT_IP'))
              $ipaddress = getenv('HTTP_CLIENT_IP');
          else if(getenv('HTTP_X_FORWARDED_FOR'))
              $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
          else if(getenv('HTTP_X_FORWARDED'))
              $ipaddress = getenv('HTTP_X_FORWARDED');
          else if(getenv('HTTP_FORWARDED_FOR'))
              $ipaddress = getenv('HTTP_FORWARDED_FOR');
          else if(getenv('HTTP_FORWARDED'))
              $ipaddress = getenv('HTTP_FORWARDED');
          else if(getenv('REMOTE_ADDR'))
              $ipaddress = getenv('REMOTE_ADDR');
          else
              $ipaddress = 'UNKNOWN';
    
          return $ipaddress;
     }
     private function contains($str,$contain)
    {
        if(stripos($contain,"|") !== false)
            {
            $s = preg_split('/[|]+/i',$contain);
            $len = sizeof($s);
            for($i=0;$i < $len;$i++)
                {
                if(stripos($str,$s[$i]) !== false)
                    {
                    return(true);
                    }
                }
            }
        if(stripos($str,$contain) !== false)
            {
            return(true);
            }
      return(false);
    }
    public function send(SendContactRequest $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);
        $lines = file(public_path('vendor/filter/gpHmA8X5.txt'));
        $search = $request->name;
        foreach($lines as $line)
        {
          if($this->contains($line,$search)){
            Session::flash('error','Adult contents are prohibited!');
            return redirect()->back();
          }
        }
        $search = $request->message;
        foreach($lines as $line)
        {
          if($this->contains($line,$search)){
            Session::flash('error','Adult contents are prohibited!');
            return redirect()->back();
          }
        }
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->number = $request->phone;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->save();

        Mail::send(new SendContact($request));

        Session::flash('alert','Contact mail sent successfully!');
        return redirect()->back();
    }
}
