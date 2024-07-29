<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryConroller extends Controller
{
    public function index(){
        $data = Category::with('getUser')->get();
        return response()->json([
            'category' => $data,
        ]);
    }

    public function store(Request $request){


        try{
            $validate =Validator::make($request->all(),[
                'name' => 'required|string',
                'user_id' => 'required',
            ]);

            if ($validate->fails()){
            return response()->json([
                'error' => $validate->errors(),
            ], 422);


            }
            $category = Category::create($request->all());
              return response()->json([
           'message' => 'Category created successfully',
            'category' => $category,
        ]);
     }
     catch(\Exception $e){
         return response()->json([
             'massage' => $e->getMessage(),
         ], 500);
     }

    }
    public function show ($id){
        $category = Category::with('getUser')->findOrFail($id);
        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Request $request,$id){
        // $category = new Category();
        // $category->name = $request->name;
        // $category->user_id = $request->user_id;
        // $category->save();

        try{
            $validate =Validator::make($request->all(),[
                'name' => 'required|string',
                'user_id' => 'required',
            ]);

            if ($validate->fails()){
            return response()->json([
                'error' => $validate->errors(),
            ], 422);


            }
            $category = Category::findOrFail($id);
            $category->update($request->all());
              return response()->json([
           'message' => 'Category upadate successfully',
            'category' => $category,
        ]);
     }
     catch(\Exception $e){
         return response()->json([
             'massage' => $e->getMessage(),
         ], 500);
     }
    }
}
