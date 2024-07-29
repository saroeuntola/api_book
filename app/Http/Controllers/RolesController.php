<?php
namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Exception;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = $request->search;
        $roles = Role::orderBy('id', 'ASC')->get();
        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);

    }

    public function getPermission()
    {
        try {
            $permission = Permission::get();
            $newResult = array();
            $mainPer = array();

            foreach ($permission as $key => $value) {
                $myArray = explode('-', $value->name);
                $mainPer[$myArray[0]] = array('name' => $myArray[0]);
            }

            foreach ($mainPer as $key => $mper) {
                $rsPerm = array();

                foreach ($permission as $key => $value) {
                    $speratePerm = explode('-', $value->name);
                    if (in_array($mper['name'], $speratePerm , true))
                    {
                        $rsPerm[] = array('id' => $value->id, 'name' => $value->name, 'description' => $value->description);
                    }
                }
                $newResult[] = array('name' => $mper['name'], 'resultpermission' => $rsPerm);
            }
            return response()->json(['message' => 'success', 'permission' => $permission, 'newlistPermission'=> $newResult], 200, [],JSON_NUMERIC_CHECK);
        } catch (\Throwable $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

public function store(Request $request)
{
    try {
        $requestData = $request->all();

        // Validation rules
        $rules = [
            'name' => 'required|unique:roles,name',
            'guard_name' => 'required',
            'permission' => 'required|array',
        ];

        // Validate the incoming data
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        // Create the role
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name
        ]);

        // Retrieve permissions based on the provided IDs
        $permissions = Permission::whereIn('id', $request->permission)->get();

        // Sync the permissions with the role
        $role->syncPermissions($permissions);

        return response()->json(['message' => 'Role created successfully with permissions', 'role' => $role, 'permissions' => $permissions], 200);
    } catch (\Throwable $ex) {
        return response()->json(['message' => $ex->getMessage()], 400);
    }
}

  public function show($id)
    {
        try
        {
            $role = Role::find($id);
            if (!$role)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], 404);
            }
            $permissions = $role->permissions()->select('id', 'name')->get();
            $permissionsData = $permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            });
            return response()->json([
                'message' => 'Success',
                "roles" => $role,
                'permission' => $permissionsData,

            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function edit($id)
    {
        try {
            $role = Role::find($id);
            $permission = Permission::get();

            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)->pluck('role_has_permissions.permission_id')->all();
            $newResult = array();
            $mainPer = array();

            foreach ($permission as $key => $value) {
                $myArray = explode('-', $value->name);
                $mainPer[$myArray[0]] = array('name' => $myArray[0]);
            }

            foreach ($mainPer as $key => $mper) {
                $rsPerm = array();

                foreach ($permission as $key => $value) {
                    $speratePerm = explode('-', $value->name);
                    if (in_array($mper['name'], $speratePerm , true))
                    {
                        $rsPerm[] = array('id' => $value->id, 'name' => $value->name, 'description' => $value->description);
                    }
                }
                $newResult[] = array('name' => $mper['name'], 'resultpermission' => $rsPerm);
            }
            return response()->json(['message' => 'Role edit successfully', 'roles' => $role, 'permission'=> $newResult, 'rolePermissions'=> $rolePermissions], 200,[],JSON_NUMERIC_CHECK);
        } catch (\Throwable $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }



 public function update($id, Request $request)
{
    try {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'guard_name' => 'required|string',
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $role->name = $request->input('name');
        $role->guard_name = $request->input('guard_name');
        $role->save();

        $permissions = $request->input('permissions', []);

        $role->syncPermissions($permissions);

        return response()->json([
            'status' => 'success',
            'message' => 'Role permissions updated successfully',
            'role' => $role,
            'permissions' => $permissions,
        ], 200);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json(['message' => 'Role not found'], 404);
            }

            // Begin a database transaction
            DB::beginTransaction();

            // Detach the permissions from the role
            $role->permissions()->detach();

            // Delete the role
            $role->delete();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Role and associated permissions deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on exception
            DB::rollBack();

            return response()->json([
                'message' => 'Error deleting role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
