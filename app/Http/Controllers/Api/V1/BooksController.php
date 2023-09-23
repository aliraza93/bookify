<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Show all of the books  
        try {
            $perPage = 20;
            $books = Book::with(['user', 'sections'])->paginate($perPage);
            return $this->SuccessResponse($this->dataRetrieved, $books);
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, null, null);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Create a new book record
            $user           = User::where('id', auth()->id())->first();
            $book           = new Book();
            $book->title    = $request->title;
            $user->books()->save();

            return $this->SuccessResponse(
                $this->dataCreated,
                [
                    'book'  => $book
                ]
            );
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }
}
