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
   
        if($validator->fails()){
            return Response(['message' => $validator->errors()], 401);
        }
   
        if(Auth::attempt($request->all())){
            $user = Auth::user(); 
            $success =  $user->createToken('MyApp')->plainTextToken; 
            return Response(['token' => $success], 200);
        }

        return Response(['message' => 'email or password wrong'], 401);
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
        if (Auth::check()) {
            $user = Auth::user();
            return Response(['data' => $user], 200);
        }

        return Response(['data' => 'Unauthorized'], 401);
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
        ]);

        if ($validator->fails()) {
            return Response(['message' => $validator->errors()], 400);
        }

        $user->update($request->all());

        return Response(['message' => 'User updated successfully'], 200);
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
