<?php
/**
 * Created by PhpStorm.
 * User: iongh
 * Date: 8/1/2018
 * Time: 3:37 PM
 */

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Login User
     *
     * @param Request $request
     * @param User $userModel
     * @param JwtToken $jwtToken
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GenTux\Jwt\Exceptions\NoTokenException
     */
    public function login(Request $request, User $userModel, JwtToken $jwtToken)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'Email empty',
            'email.email' => 'Email invalid',
            'password.required' => 'Password empty'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if (!$validator->passes()) {
            return $this->returnBadRequest();
        }

        $user = $userModel->login($request->email, $request->password);

        if (!$user) {
            return $this->returnNotFound('User sau parola gresite');
        }

        $token = $jwtToken->createToken($user);

        $data = [
            'user' => $user,
            'jwt' => $token->token()
        ];

        return $this->returnSuccess($data);
    }

    public function register(Request $request, User $userModel, JwtToken $jwtToken)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ];

        $messages = [
            'name.required' => 'Name empty',
            'email.required' => 'Email empty',
            'email.email' => 'Email invalid',
            'password.required' => 'Password empty',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if (!$validator->passes()) {
            return $this->returnBadRequest();
        }

        $user = $userModel->register($request->name, $request->email, $request->password);

        if (!$user) {
            return $this->returnNotFound('error');
        }

        $token = $jwtToken->createToken($user);

        $data = [
            'user' => $user,
            'jwt' => $token->token()
        ];

        return $this->returnSuccess($data);
    }


    public function changeType(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->roles()->detach();
        if ($request->type == 1)
            $user->roles()->attach(Role::where('name', 'normal')->first());
        else if ($request->type == 2)
            $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }


    public function verify($id){
        $user = User::where('id', $id)->first();
        $user->status = 1;
        $user->update();

        return $user;
    }
}