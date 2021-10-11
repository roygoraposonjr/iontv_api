<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('categories', function() {
    // If the Content-Type and Accept headers are set to 'application/json', 
    // this will return a JSON structure. This will be cleaned up later.
    return Category::all();
});
Route::get('category/{category}/channels', function(Category $category) {
    // If the Content-Type and Accept headers are set to 'application/json', 
    // this will return a JSON structure. This will be cleaned up later.
   
     return $category->channels()->get();
});

Route::get('category/channels', function() {
    // If the Content-Type and Accept headers are set to 'application/json', 
    // this will return a JSON structure. This will be cleaned up later.
    $categories = Category::all();
    $data = [];
    foreach ($categories as $key => $category) {
        $data[$category->name]=$category->channels()->get();
    }

    return $data;
});



Route::get('channels', function() {
    // If the Content-Type and Accept headers are set to 'application/json', 
    // this will return a JSON structure. This will be cleaned up later.
    return Channel::all();
});