<?php

namespace App\Http\Controllers;

use App\Mail\MailVerifyLinkSender;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\Station;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        return view('ceo.index');
    }

    public function viewUsers(Request $request)
    {
        $users = User::where('verified', 1)->paginate(5);

        return view('ceo.viewUsers')
            ->with('users', $users);
    }

    public function unverifiedUsers(Request $request)
    {
        $users = User::where('verified', 0)->paginate(5);

        return view('ceo.unverifiedUsers')
            ->with('users', $users);
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::find($id);
        $user->verified = 1;
        $user->update();

        $request->session()->flash('success_message', 'User verified successfully.');

        return redirect()->route('admin.viewUsers');
    }

    public function unverifyUser(Request $request, $id)
    {
        $user = User::find($id);
        $user->verified = 0;
        $user->update();

        $request->session()->flash('success_message', 'User unverified successfully.');

        return redirect()->back();
    }

    public function createUser(Request $request)
    {
        $stations = Station::all();
        $permissions = Permission::all();

        $user_types = [
            '0' => 'Receptionist',
            '1' => 'Employee',
            '2' => 'Manager',
            '3' => 'CEO',
            '4' => 'System Admin',
        ];

        return view('ceo.createUser')
            ->with('user_types', $user_types)
            ->with('stations', $stations)
            ->with('permissions', $permissions);
    }

    public function createUserSubmit(Request $request)
    {
        $this->validate(
            $request,
            [
                'verified' => 'required|numeric|regex:/^[0-1]$/i',
                'name' => 'required|min:3',
                'username' => 'required|min:3|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'hire_date' => 'required|date|date_format:Y-m-d',
                'type' => 'required|numeric|min:0|max:4',
                'salary' => 'required|numeric|min:0',
                'station_id' => 'required|numeric|exists:stations,id',
                'permission_id' => 'required|numeric|exists:permissions,id',
            ]
        );

        // return $request->input();

        $station = Station::find($request->station_id);

        if (!$station) {
            $request->session()->flash('error_message', 'Station not found.');
            return redirect()->back();
        }

        $rnd_int = random_int(100000, 999999);

        $address = new Address();
        $address->local_address = $request->local_address;
        $address->police_station = $request->police_station;
        $address->city = $request->city;
        $address->country = $request->country;
        $address->zip_code = $request->zip_code;
        $address->save();

        $user = new User();

        $user->verified = $request->verified;
        $user->verify_email = $rnd_int;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->type = $request->type;
        $user->email = $request->email;
        $user->password = Crypt::encrypt($rnd_int);
        $user->salary = $request->salary;
        $user->hire_date = $request->hire_date;
        $user->address_id = $address->id;
        $user->station_id = $station->id;
        $user->save();

        Mail::to($request->email)
            ->send(new MailVerifyLinkSender('Verify Email', $user->id, $rnd_int));


        $request->session()->flash('success_message', "User created successfully. A verification link has been sent to the user's email.");

        return redirect()->route('admin.viewUsers');
    }

    public function sendEmailVerifyLink(Request $request)
    {
        return view('ceo.sendEmailVerifyLink');
    }

    public function sendEmailVerifyLinkSubmit(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->verify_email === 0) {
            $request->session()->flash('error_message', 'User already verified.');
            return redirect()->back();
        }

        $rnd_int = random_int(100000, 999999);

        $user->verify_email = $rnd_int;
        $user->password = Crypt::encrypt($rnd_int);
        $user->update();

        Mail::to($request->email)
            ->send(new MailVerifyLinkSender('Verify Email', $user->id, $rnd_int,));

        $request->session()->flash('success_message', "A verification link has beed sent to $user->email");

        return redirect()->back();
    }
}
