<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return response()->json(Author::with('books')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email',
            'bio' => 'nullable|string',
        ]);

        return response()->json(Author::create($data), 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load('books'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:authors,email,' . $author->id,
            'bio' => 'nullable|string',
        ]);

        $author->update($data);

        return response()->json($author);
    }

    public function destroy(Author $author)
    {
        $author->delete();

        return response()->json([
            'message' => 'Author berhasil dihapus'
        ]);
    }
}