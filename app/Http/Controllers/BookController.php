<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



use function Laravel\Prompts\text;

class BookController extends Controller
{
    // public function __construct(){
    //     $this->middleware('permission:book-list|book-create|book-edit|book-delete', ['only' => ['index','store']]);
    //     $this->middleware('permission:book-create', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:book-edit', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:book-delete', ['only' => ['destroy']]);
    // }
    public function index(){
        try{
            $book = Book::with('getCategory','getUser')->get();
            return response()->json(['message'=>'success', 'book'=>$book]);
        }
        catch(\Exception $ex){

            return response()->json(['message'=>'error', 'error'=> $ex->getMessage()]);
        }


    }

  public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'image' => 'required|nullable', // Validate image file
            'link' => 'required|string',
            'user_id' => 'required|integer',
            'category_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }


        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Delete old image if it exists
            if ($request->has('old_image') && $request->old_image) {
                $oldImagePath = public_path('book_images') . '/' . $request->old_image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('book_images'), $imageName);
            $bookData['image'] = $imageName;
        }

        $book = Book::create([
            'name' => $request->name,
            'image' => $bookData['image'] ?? null,
            'link' => $request->link,
            'user_id' => $request->user_id,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => 'Created successfully', 'book' => $book], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function show ($id){
    try{
        $book = Book::with('getCategory','getUser')->findOrFail($id);
        return response()->json(['message'=>'success', 'book'=>$book]);
    }
    catch(\Exception $ex){

        return response()->json(['message'=>'error', 'error'=> $ex->getMessage()]);
    }
}







public function update(Request $request, $id)
{
    try {
        $book = Book::findOrFail($id);

        $validate = validator::make($request->all(),[
            'name' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file types and size as needed
            'link' => 'required|string',
            'user_id' => 'required|integer',
            'category_id' => 'required|integer',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()], 400);
        }

        $bookData = $request->only(['name', 'link', 'user_id', 'category_id']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Delete old image if it exists
            if ($book->image) {
                $oldImagePath = public_path('book_images') . '/' . $book->image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('book_images'), $imageName);
            $bookData['image'] = $imageName;
        }

        // Update the book
        $book->update($bookData);

        // Return a successful JSON response
        return response()->json(['message' => 'Updated successfully', 'book' => $book], 200);
    } catch (\Exception $e) {
        // Return an error JSON response
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




public function destroy($id){
    try {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        if ($book->image && Storage::exists($book->image)) {
                Storage::delete($book->image);
         }

        $book->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }
    catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




}
