<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }
        // Show all of the sections with sub sections  
        try {
            // Check if book exists
            $book = Book::where('id', $request->book_id)->first();
            if (!$book) {
                return $this->ErrorResponse('Invalid book id provided.', null, null);
            }
            $perPage = 20;
            $sections = Section::with(['parent', 'children'])->paginate($perPage);
            return $this->SuccessResponse($this->dataRetrieved, $sections);
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, null, null);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'book_id' => 'required|integer',
            'parent_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Create a new section
            $user = User::where('id', auth()->id())->first();

            // Check if book exists
            $book = Book::where('id', $request->book_id)->first();
            if (!$book) {
                return $this->ErrorResponse('Invalid book id provided.', null, null);
            }

            // Check if it is my book
            $bookIds = Book::where('user_id', $request->user_id)->pluck('id');
            if (!array_key_exists($request->book_id, $bookIds)) {
                return $this->ErrorResponse('You can create a section in your own book only!', null, null);
            }

            // Check if user has roles to create section
            if (!$user->hasRole('author') && !$user->hasPermissionTo('create section')) {
                return $this->ErrorResponse('No access granted for creating section', null, null);
            }

            // Create a section/sub section
            $section = new Section();
            $section->title = $request->title;
            $section->content = $request->content;
            $section->parent_id = $request->parent_id;
            $book->sections()->create($section);

            return $this->SuccessResponse(
                $this->dataCreated,
                [
                    'date' => $section
                ]
            );
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }

    public function changeAccessToCollaborator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'section_id' => 'required|integer',
            'grant_access' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Create a new section
            $user = User::where('id', auth()->id())->first();

            // Check if collaborator exists
            $collaborator = User::where('id', $request->user_id)->first();
            if (!$collaborator) {
                return $this->ErrorResponse('Invalid collaborator id provided.', null, null);
            }

            // Check if section exists
            $section = Section::where('id', $request->section_id)->first();
            if (!$section) {
                return $this->ErrorResponse('Invalid section id provided.', null, null);
            }

            // Check if user has roles to create section
            if (!$user->hasRole('author')) {
                return $this->ErrorResponse('No access granted for granting permission!', null, null);
            }

            // Check if permission is to be granted or revoked
            if ($request->grant_access) {
                if (!$collaborator->hasRole('collaborator')) {
                    // Grant edit section permission to collaborator
                    $collaborator->assignRole('collaborator');
                    $collaborator->givePermissionTo('edit section');

                    $section->collaborators()->attach($collaborator);

                    return $this->SuccessResponse(
                        $this->dataUpdated,
                        null
                    );
                }
            } else {
                if ($collaborator->hasRole('collaborator')) {
                    if ($collaborator->hasPermissionTo('edit section')) {
                        $collaborator->revokePermissionTo('edit section');
                        $section->collaborators()->detach($collaborator);
                    }

                    return $this->SuccessResponse(
                        $this->dataUpdated,
                        null
                    );
                }
            }
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'section_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Create a new section
            $user = User::where('id', auth()->id())->first();

            // Check if section exists
            $section = Section::where('id', $request->section_id)->first();
            if (!$section) {
                return $this->ErrorResponse('Invalid section id provided.', null, null);
            }

            // Check if user has roles to edit section
            if (!($user->hasAnyRole(['author', 'collaborator'])) && !$user->hasPermissionTo('edit section')) {
                return $this->ErrorResponse('No access granted for updating section', null, null);
            }

            // Create a section/sub section
            $section->title = $request->title;
            $section->content = $request->content;
            $section->save();

            return $this->SuccessResponse(
                $this->dataUpdated,
                [
                    'date' => $section
                ]
            );
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }
}
