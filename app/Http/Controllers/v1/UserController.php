<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Role;
use App\Task;
use App\User;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\v1
 */
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
     */
    public function login(Request $request, User $userModel, JwtToken $jwtToken)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $user = $userModel->login($request->email, $request->password);

            if (!$user) {
                return $this->returnNotFound('Invalid credentials');
            }

            if ($user->status === User::STATUS_INACTIVE) {
                return $this->returnError('User is not approved by admin');
            }

            $token = $jwtToken->createToken($user);

            $data = [
                'user' => $user,
                'token' => $token->token()
            ];

            return $this->returnSuccess($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Register user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->status = User::STATUS_INACTIVE;
            $user->role_id = Role::ROLE_USER;

            $user->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Forgot password
     *
     * @param Request $request
     * @param User $userModel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request, User $userModel)
    {
        try {
            $rules = [
                'email' => 'required|email|exists:users'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $user = $userModel::where('email', $request->email)->get()->first();

            $user->forgot_code = strtoupper(str_random(6));
            $user->save();

            //TODO should sent an email to user with code

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @param User $userModel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request, User $userModel)
    {
        try {
            $rules = [
                'email' => 'required|email|exists:users',
                'code' => 'required',
                'password' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $user = $userModel::where('email', $request->email)->where('forgot_code', $request->code)->get()->first();

            if (!$user) {
                $this->returnNotFound('Code is not valid');
            }

            $user->password = Hash::make($request->password);
            $user->forgot_code = '';

            $user->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Update logged user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $user = $this->validateSession();

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return $this->returnSuccess($user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function getTasks()
    {
        try {
            $tasks = Task::where('user_id', $this->validateSession()->id)->orWhere('assign', $this->validateSession()->id)->get();
            return $this->returnSuccess($tasks);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function createTask(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'description' => 'required',
                'status' => 'required|integer',
                'assign' => 'required:users'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $task = new Task();
            $task->name = $request->name;
            $task->description = $request->description;
            $task->status = $request->status;
            $task->user_id = $this->validateSession()->id;
            $task->assign = $request->assign;

            $task->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Get logged user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        try {
            $user = $this->validateSession();

            return $this->returnSuccess($user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function updateTask($id, Request $request)
    {
        try {
            if (!$task = Task::find($id))
                return $this->returnNotFound("Task not found");

            if ($task->user_id != $this->validateSession()->id)
                return $this->returnError('This task do not belongs to you');

            if ($request->has('name'))
                $task->name = $request->name;

            if ($request->has('description'))
                $task->description = $request->description;

            if ($request->has('status'))
                $task->status = $request->status;

            if ($request->has('assign'))
                $task->assign = $request->assign;

            $task->update();

            return $this->returnSuccess($task);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

}