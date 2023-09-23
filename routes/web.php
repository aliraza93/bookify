<?php

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Crete Roles and permissions
Route::get('create-roles-permissions', function () {
    $author = Role::create(['name' => 'author']);
    $collaborator = Role::create(['name' => 'collaborator']);
    $permission1 = Permission::create(['name' => 'edit section']);
    $permission2 = Permission::create(['name' => 'create section']);

    // Give create section and edit section permission to author
    $author->givePermissionTo($permission1);
    $author->givePermissionTo($permission2);
});
