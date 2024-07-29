<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class CountController extends Controller
{
    public function index()
    {
        try {
            $userCount = User::count();
            $bookCount = Book::count();
            $categoryCount = Category::count();
            return response()->json([
                'total_user' => $userCount,
                'total_book' => $bookCount,
                'total_category' => $categoryCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching counts.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
