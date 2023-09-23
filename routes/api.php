<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BooksController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\SectionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1', 'namespace' => 'Api\V1'], function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::post('register', [AuthController::class, 'register']);

    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | Sections and Sub Sections Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('sections')->group(function () {
            Route::get('/', [SectionsController::class, 'index']);
            Route::post('/create', [SectionsController::class, 'store']);

            // Grant access to collaborator
            Route::post('update-access', [SectionController::class, 'changeAccessToCollaborator']);
        });

        /*
        |--------------------------------------------------------------------------
        | Books Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('books')->group(function () {
            Route::get('/', [BooksController::class, 'index']);
            Route::post('/create', [BooksController::class, 'create']);
            Route::get('/details', [BooksController::class, 'show']);
        });
    });
});
