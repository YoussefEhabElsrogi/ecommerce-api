<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\UserHelper;
use App\Helpers\ApiResponse;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLoginUserRequest;
use App\Http\Requests\Api\StoreRegisterUserRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        // Apply the 'auth.user' middleware to all methods except 'login' and 'register'
        $this->middleware('auth.user', ['except' => ['login', 'register']]);
    }

    public function register(StoreRegisterUserRequest $request)
    {
        // Validate request data
        $data = $request->validated();

        // Check if an image file is provided and handle the upload
        if ($request->hasFile('image')) {
            $data['image'] = ImageHelper::uploadImage('users', $request->file('image'));
        }

        // Hash the password before saving
        $data['password'] = Hash::make($data['password']);

        // Create a new user with the validated data
        $user = User::create($data);

        // Return a success response with the created user data
        return ApiResponse::sendResponse(201, 'User Created Successfully', new UserResource($user));
    }

    public function login(StoreLoginUserRequest $request)
    {
        // Validate request data
        $credentials = $request->validated();

        // Attempt to authenticate the user using the provided credentials
        if (!$token = auth()->guard('user')->attempt($credentials)) {
            // If authentication fails, return an error response
            return ApiResponse::sendResponse(401, 'The Email Or Password Is Incorrect', []);
        }

        // Get the authenticated user
        $user = Auth::guard('user')->user();

        // Generate a JWT token with additional claims
        $token = JWTAuth::claims([
            'id' => $user->id,
            'user name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
        ])->fromUser($user);

        // Return a success response with the generated token
        return ApiResponse::sendResponse(200, 'User Logged In Successfully', [$this->createNewToken($token)]);
    }

    protected function createNewToken($token)
    {
        // Get the authenticated user
        $user = Auth::guard('user')->user();

        // Return the token and user details
        return [
            'token' => $token,
            'id' => $user->id,
            'user name' => $user->first_name  . ' '  . $user->last_name,
            'email' => $user->email,
            'image' => [
                'url' => $user->image,
            ],
        ];
    }

    public function logout($user_id)
    {
        // Check if user is found
        $result = UserHelper::checkUserFound($user_id);
        if ($result['status'] !== 200) {
            // If user is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate user ID and authorize user
        if (!UserHelper::authorizeUser($user_id)) {
            // If validation or authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Perform logout
        auth()->logout();

        // Return success response
        return ApiResponse::sendResponse(200, 'User Logged Out Successfully', []);
    }

    public function showProfile()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Return a success response with the user profile data
        return ApiResponse::sendResponse(200, 'Profile Data Retrieved Successfully', new UserResource($user));
    }

    public function updateProfile(UpdateProfileUserRequest $request, $user_id)
    {
        // Check if user is found
        $result = UserHelper::checkUserFound($user_id);
        if ($result['status'] !== 200) {
            // If user is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize user
        if (!UserHelper::authorizeUser($user_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the user data
        $user = $result['data'];

        // Validate the request data
        $data = $request->validated();

        // Check if a new image is provided
        if ($request->hasFile('image')) {
            // Handle old image deletion
            ImageHelper::handleImageUpdate($user, 'users');
            // Upload the new image and store the path in the data array
            $data['image'] = ImageHelper::uploadImage('users', $request->file('image'));
        }

        // Update the user profile with the validated data
        $user->update($data);

        // Return success response with the updated user data
        return ApiResponse::sendResponse(200, 'Profile updated successfully', new UserResource($user));
    }

    public function updatePassword(UpdatePasswordRequest $request, $user_id)
    {
        // Check if user is found
        $result = UserHelper::checkUserFound($user_id);
        if ($result['status'] !== 200) {
            // If user is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize user
        if (!UserHelper::authorizeUser($user_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the user data
        $user = $result['data'];

        // Validate the request data
        $data = $request->validated();

        // Check if the current password is correct
        if (!UserHelper::checkCurrentPassword($user, $data['current_password'])) {
            // If current password is incorrect, return an error response
            return ApiResponse::sendResponse(400, 'Current password is incorrect', []);
        }

        // Update the user's password
        $user->password = Hash::make($data['new_password']);
        $user->save();

        // Return success response
        return ApiResponse::sendResponse(200, 'Password updated successfully', []);
    }

    public function destroy($user_id)
    {
        // Check if user is found
        $result = UserHelper::checkUserFound($user_id);
        if ($result['status'] !== 200) {
            // If user is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize user
        if (!UserHelper::authorizeUser($user_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the user data
        $user = $result['data'];

        // Handle old image deletion
        ImageHelper::handleImageUpdate($user, 'users');

        // Delete the user record
        $user->delete();

        // Return success response
        return ApiResponse::sendResponse(200, 'User Deleted Successfully', []);
    }
}
