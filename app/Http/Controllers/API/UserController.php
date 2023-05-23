<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function loginUser(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 401);
        }
    
        $credentials = $request->only(['email', 'password']);
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $success = [
                'token' => $user->createToken('MyApp')->plainTextToken,
                'usertype' => $user->usertype,
            ];
            return response($success, 200);
        }
    
        return response(['message' => 'Invalid email or password'], 401);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function createUser(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'usertype' => 'required|in:admin,visitor',
            'phoneno'  => 'required',
        ]);

        if ($validator->fails()) {
            return Response(['message' => $validator->errors()], 400);
        }

        $user = User::create($request->all());

        $success = $user->createToken('MyApp')->plainTextToken;

        return Response(['token' => $success], 201);
    }

    /**
     * Display the specified resource.
     */
    public function userDetails(): Response
    {
        try{
            if (Auth::check()) {
                $users = User::all();
                return Response(['data' => $users], 200);
            }
            return Response(['message' => 'Unauthorized. Please log in.'], 400);
        } catch(\Exception $e){
            return response(['message' => 'Unauthorized access'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUser(Request $request, $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'required|min:6',
            'usertype' => 'required|in:admin,visitor',
            'phoneno'  => 'required',
        ]);

        if ($validator->fails()) {
            return Response(['message' => $validator->errors()], 400);
        }

        $user->update($request->all());

        return Response(['message' => 'User updated successfully'], 200);
    }

    public function toggleUserType($id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response(['message' => 'User not found'], 404);
        }

        $user->usertype = ($user->usertype === 'admin') ? 'visitor' : 'admin';
        $user->save();

        return Response(['message' => 'User usertype toggled successfully'], 200);
    }

    /**
     * Logout the user.
     */
    public function logout(): Response
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();
        
        return Response(['data' => 'User logged out successfully'], 200);
    }
}
