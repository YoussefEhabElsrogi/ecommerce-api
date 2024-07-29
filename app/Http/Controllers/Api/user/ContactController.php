<?php

namespace App\Http\Controllers\Api\user;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Resources\Api\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    public function __construct()
    {
        // Apply the 'auth.admin' middleware to all methods except 'login' and 'register'
        $this->middleware('auth.admin', ['except' => 'store']);
    }
    public function index()
    {
        $contacts = Contact::all();

        if ($contacts->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No Contacts Yet');
        }

        $contactsResource = ContactResource::collection($contacts);

        return ApiResponse::sendResponse(200, 'Contact Reterived Successfully', $contactsResource);
    }
    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();

        // Create a new contact entry
        $contact = Contact::create($data);

        // Check if the contact was created successfully
        if (!$contact) {
            return ApiResponse::sendResponse(500, 'An error occurred while saving contact information');
        }

        $contactResource = new ContactResource($contact);

        return ApiResponse::sendResponse(201, 'Contact information saved successfully', $contactResource);
    }
    public function show($id)
    {
        $contact = Contact::where('id', $id)->first();

        if (!$contact) {
            return ApiResponse::sendResponse(404, 'Contact Not Found', []);
        }

        $contactResource = new ContactResource($contact);

        return ApiResponse::sendResponse(200, 'Contact Retrieved Successfully', $contactResource);
    }
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return ApiResponse::sendResponse(404, 'Contact Not Found', []);
        }

        $contact->delete();

        return ApiResponse::sendResponse(200, 'Contact Deleted Successfully');
    }
}
