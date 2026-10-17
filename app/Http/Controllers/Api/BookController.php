<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'categories']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;

            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'published_year' => 'required|integer|min:1000|max:2100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $book = Book::create($data);
        $book->categories()->sync($categoryIds);

        return response()->json(
            $book->load(['author', 'categories']),
            201
        );
    }

    public function show(Book $book)
    {
        return response()->json($book->load(['author', 'categories']));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'isbn' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('books', 'isbn')->ignore($book->id),
            ],
            'published_year' => 'sometimes|required|integer|min:1000|max:2100',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'author_id' => 'sometimes|required|exists:authors,id',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $book->update($data);

        if ($categoryIds !== null) {
            $book->categories()->sync($categoryIds);
        }

        return response()->json(
            $book->load(['author', 'categories'])
        );
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book berhasil dihapus'
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'total_books' => Book::count(),
            'total_authors' => \App\Models\Author::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_stock' => Book::sum('stock'),
            'average_price' => round((float) Book::avg('price'), 2),
        ]);
    }
}