<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(Book::with('author', 'categories')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book = Book::create($data);
        $book->categories()->sync($data['category_ids'] ?? []);

        return response()->json($book->load('author', 'categories'), 201);
    }

    public function show(Book $book)
    {
        return response()->json($book->load('author', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'published_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book->update($data);
        if (isset($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return response()->json($book->load('author', 'categories'));
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}