<?php

namespace App\Http\Controllers;


use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
  
  public function showAllAuthors() {
    return response()->json([
      'data' => Author::all()
    ], 200);
  }

  public function showOneAuthor($id) {
    $author = Author::find($id);

    if (!$author) {
      return response()->json([
        'error' => 'author not found'
      ], 404);
    }

    return response()->json([
      'data' => $author
    ], 200);
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required|email|unique:authors',
      'location' => 'required|alpha'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'error' => 'validation failed',
        'detail' => $validator->messages()
      ], 400);
    }

    $author = Author::create($request->all());

    return response()->json([
      'data' => $author
    ], 201);
  }

  public function update($id, Request $request) {
    $author = Author::findOrFail($id);
    $author->update($request->all());

    return response()->json([
      'data' => $author
    ], 200);
  }

  public function delete($id) {
    $author = Author::find($id);

    if (!$author) {
      return response()->json([
        'error' => 'author not found'
      ]);
    }
    
    $author->delete();

    return response()->json(null, 204);
  }

}
