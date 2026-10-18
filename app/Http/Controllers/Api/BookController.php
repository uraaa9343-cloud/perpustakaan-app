<?php

namespace App\Http\Controllers\Api;

use App\Models\Author;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
{
    $query = Book::with('author', 'categories');

    // Cari berdasarkan judul atau nama penulis
    if ($request->filled('search')) {
        $s = $request->search;
        $query->where(function ($q) use ($s) {
            $q->where('title', 'like', "%$s%")
              ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%$s%"));
        });
    }

    // Filter kategori
    if ($request->filled('category_id')) {
        $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category_id));
    }

    // Filter stok tersedia (stok > 0)
    if ($request->boolean('in_stock')) {
        $query->where('stock', '>', 0);
    }

    return response()->json($query->get());
}

// Buku dengan stok menipis (< 5)
public function lowStock()
{
    return response()->json(Book::with('author')->where('stock', '<', 5)->get());
}

// Statistik
public function stats()
{
    return response()->json([
        'total_books' => Book::count(),
        'total_authors' => Author::count(),
        'total_categories' => Category::count(),
    ]);
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