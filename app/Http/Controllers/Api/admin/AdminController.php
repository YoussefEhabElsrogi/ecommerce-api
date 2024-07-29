<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\AdminHelper;
use App\Helpers\ApiResponse;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLoginAdminRequest;
use App\Http\Requests\Api\StoreRegisterAdminRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function __construct()
    {
        // Apply the 'auth.admin' middleware to all methods except 'login' and 'register'
        $this->middleware('auth.admin', ['except' => ['login', 'register']]);
    }

    public function register(StoreRegisterAdminRequest $request)
    {
        // Validate request data
        $data = $request->validated();

        // Check if an image file is provided and handle the upload
        if ($request->hasFile('image')) {
            $data['image'] = ImageHelper::uploadImage('admins', $request->file('image'));
        }

        // Hash the password before saving
        $data['password'] = Hash::make($data['password']);

        // Create a new admin with the validated data
        $admin = Admin::create($data);

        // Return a success response with the created admin data
        return ApiResponse::sendResponse(201, 'Admin Created Successfully', new AdminResource($admin));
    }

    public function login(StoreLoginAdminRequest $request)
    {
        // Validate request data
        $credentials = $request->validated();

        // Attempt to authenticate the admin using the provided credentials
        if (!$token = auth()->guard('admin')->attempt($credentials)) {
            // If authentication fails, return an error response
            return ApiResponse::sendResponse(401, 'The Email Or Password Is Incorrect', []);
        }

        // Get the authenticated admin
        $admin = Auth::guard('admin')->user();

        // Generate a JWT token with additional claims
        $token = JWTAuth::claims([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
        ])->fromUser($admin);

        // Return a success response with the generated token
        return ApiResponse::sendResponse(200, 'Admin Logged In Successfully', [$this->createNewToken($token)]);
    }

    protected function createNewToken($token)
    {
        // Get the authenticated admin
        $admin = Auth::guard('admin')->user();

        // Return the token and admin details
        return [
            'token' => $token,
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'image' => [
                'url' => $admin->image,
            ],
        ];
    }

    public function logout($admin_id)
    {
        // Check if admin is found
        $result = AdminHelper::checkAdminFound($admin_id);
        if ($result['status'] !== 200) {
            // If admin is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate admin ID and authorize admin
        if (!AdminHelper::authorizeAdmin($admin_id)) {
            // If validation or authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Perform logout
        auth()->guard('admin')->logout();

        // Return success response
        return ApiResponse::sendResponse(200, 'Admin Logged Out Successfully', []);
    }

    public function showProfile()
    {
        // Get the authenticated admin
        $admin = Auth::guard('admin')->user();

        // Return a success response with the admin profile data
        return ApiResponse::sendResponse(200, 'Profile Data Retrieved Successfully', new AdminResource($admin));
    }

    public function updateProfile(UpdateProfileAdminRequest $request, $admin_id)
    {
        // Check if admin is found
        $result = AdminHelper::checkAdminFound($admin_id);
        if ($result['status'] !== 200) {
            // If admin is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize admin
        if (!AdminHelper::authorizeAdmin($admin_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the admin data
        $admin = $result['data'];

        // Validate the request data
        $data = $request->validated();

        // Check if a new image is provided
        if ($request->hasFile('image')) {
            // Handle old image deletion
            ImageHelper::handleImageUpdate($admin, 'admins');
            // Upload the new image and store the path in the data array
            $data['image'] = ImageHelper::uploadImage('admins', $request->file('image'));
        }

        // Update the admin profile with the validated data
        $admin->update($data);

        // Return success response with the updated admin data
        return ApiResponse::sendResponse(200, 'Profile updated successfully', new AdminResource($admin));
    }

    public function updatePassword(UpdatePasswordRequest $request, $admin_id)
    {
        // Check if admin is found
        $result = AdminHelper::checkAdminFound($admin_id);
        if ($result['status'] !== 200) {
            // If admin is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize admin
        if (!AdminHelper::authorizeAdmin($admin_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the admin data
        $admin = $result['data'];

        // Validate the request data
        $data = $request->validated();

        // Check if the current password is correct
        if (!AdminHelper::checkCurrentPassword($admin, $data['current_password'])) {
            // If current password is incorrect, return an error response
            return ApiResponse::sendResponse(400, 'Current password is incorrect', []);
        }

        // Update the admin's password
        $admin->password = Hash::make($data['new_password']);
        $admin->save();

        // Return success response
        return ApiResponse::sendResponse(200, 'Password updated successfully', []);
    }

    public function destroy($admin_id)
    {
        // Check if admin is found
        $result = AdminHelper::checkAdminFound($admin_id);
        if ($result['status'] !== 200) {
            // If admin is not found, return the appropriate error response
            return ApiResponse::sendResponse($result['status'], $result['message'], $result['data']);
        }

        // Validate and authorize admin
        if (!AdminHelper::authorizeAdmin($admin_id)) {
            // If authorization fails, return forbidden response
            return ApiResponse::sendResponse(403, 'Forbidden', []);
        }

        // Get the admin data
        $admin = $result['data'];

        // Handle old image deletion
        ImageHelper::handleImageUpdate($admin, 'admins');

        // Delete the admin record
        $admin->delete();

        // Return success response
        return ApiResponse::sendResponse(200, 'Admin Deleted Successfully', []);
    }
}
