<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function __construct(){
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store','update','destroy']]);
        $this->middleware('permission:user-create', ['only' => ['store']]);
        $this->middleware('permission:user-edit', ['only' => ['update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        $users = User::all();

        return response()->json( [
            'status' => 'success',
            'data' => $users,
        ]);
    }


    public function create(){
        try{
            $roles = Role::pluck('name','name')->all();
            return response()->json(['data' => $roles]);
        }catch(\Exception $e){
            return response()->json([
               'status' => 'error',
               'message' => $e->getMessage(),
            ]);
        }

    }
public function store(Request $request)
{
    $validated = $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'roles' => 'required'
    ]);

    $user = User::create([
        'username' => $validated['username'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    $user->assignRole($request->input('roles'));
    return response()->json([
        'access' => true,
        'message' => 'User created.',
        'user' => $user
    ]);
}

   public function show($id)
{
    $user = User::with('roles')->findOrFail($id);

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404);
    }

    $userData = [
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'roles' => $user->roles,
    ];

    return response()->json([
        'status' => 'success',
        'data' => $userData,
    ]);
}

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'email' => 'required|email',
                'roles' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            $user->update($request->all());
            $user->syncRoles($request->input('roles'));
            return response()->json(['message' => 'Update successful', 'data' => $user], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
public function updatePassword(Request $request, $id)
{
    try {
        $validator = Validator::make($request->all(), [
            'password' => '|required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'Update successful', 'data' => $user], 200);
    } catch (\Exception $ex) {
        return response()->json(['message' => $ex->getMessage()], 500);
    }

}

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted.',
        ]);
    }


   public function updateStatus($id)
{
    try {
        $user = User::find($id);
        if ($user) {
            $newStatus = $user->status === 'Active' ? 'Inactive' : 'Active';
            $user->status = $newStatus;
            $user->save();
            return response()->json(['message' => 'User status updated.']);
        } else {
            return response()->json(['message' => 'User not found.'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}


    public function uploadProfileImage(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'image' =>'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Delete old image if it exists
            if ($request->has('old_image') && $request->old_image) {
                $oldImagePath = public_path('profiles_images') . '/' . $request->old_image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('profiles_images'), $imageName);
            $bookData['image'] = $imageName;
            $user->save(
                [
                    'image' => $bookData['image']?? null,
                ]
            );
        }
    }

}
