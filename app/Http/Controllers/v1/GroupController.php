<?php

namespace App\Http\Controllers\v1;

use App\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class TaskController
 *
 * @package App\Http\Controllers\v1
 */
class GroupController extends Controller
{
    /**
     * Get group lists
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        try {
            $user = $this->validateSession();
            //takes owner groups

            $groups = Group::with('users')->where('owner', $user->id)->get();

//            $groups = User::with('groups')->get();
            return $this->returnSuccess($groups);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Create a group
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $user = $this->validateSession();

            $rules = [
                'name' => 'required',
                'users' => 'nullable|array',
                'users.*' => 'nullable|exists:users,id'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes())
                return $this->returnBadRequest('Please fill all required fields');

            $group = new Group();
            $group->name = $request->name;
            $group->owner = $user->id;
            $group->save();
            $group->users()->attach($user->id);

            if ($request->has('users')) {
                $users = collect($request->users);
                $group->users()->attach($users);
            }

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    public function addParticipants(Request $request, $id)
    {
        try {

            $rules = [
                'users' => 'nullable|array',
                'users.*' => 'nullable|exists:users,id'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes())
                return $this->returnBadRequest('Please fill all required fields');

            $group = Group::find($id);

            if ($request->has('users')) {
                $users = collect($request->users);
                $group->users()->attach($users);
            }

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    /**
     * Update group
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {

            $rules = [
                'name' => 'required'
            ];

            $group = Group::find($id);

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes())
                return $this->returnBadRequest('Please fill all required fields');

            if ($request->has('name'))
                $group->name = $request->name;

            $group->save();
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    /**
     * Delete group
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        try {
            $user = $this->validateSession();
            $group = Group::find($id);

            if ($user->id !== $group->owner) {
                return $this->returnError('You don\'t have permission to do this action');
            }


            if ($request->has('user_id')) {
                $group->users()->detach($request->user_id);
                return $this->returnSuccess();
            }

            $group->users()->detach();
            $group->delete();
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
}