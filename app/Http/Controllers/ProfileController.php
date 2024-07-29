<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
 public function UpadteImage(Request $request, $id){

        try{
            $user = User::find($id);

            $validate = Validator::make($request->all(),[
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if($validate->fails()){
                return response()->json(['errors' => $validate->errors()], 422);
            }
         if ($request->hasFile('image')) {
            $image = $request->file('image');
            if ($request->has('old_image') && $request->old_image) {
                $oldImagePath = public_path('profile_images') . '/' . $request->old_image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('book_images'), $imageName);
            $bookData['image'] = $imageName;
        }
        $user = User::create(
            [
            'image' => $bookData['image']?? null,
            ]
        );
        return response()->json(['message' => 'Created successfully', 'user' => $user], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
