<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permissions-edit|permission-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:permission-create', ['only' => ['store']]);
        $this->middleware('permission:permission-edit', ['only' => ['update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $permissions = Permission::all();
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $type = explode('-', $permission->name)[0];
            if (!isset($groupedPermissions[$type])) {
                $groupedPermissions[$type] = [];
            }
            $groupedPermissions[$type][] = [
                'id' => $permission->id,
                'name'=>$permission->name
            ];

        }
        $transformedData = [];
        foreach ($groupedPermissions as $type => $fields) {
            $transformedData[] = [
                'title' => ucfirst($type),
                'permission_names' => $fields,
            ];
        }
        return response()->json([
            'status' => 'success',
            'permissions' => $transformedData
        ]);
    }
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'guard_name' => 'required|string',
        ];
        $inputs = $request->only(['name', 'guard_name']);

        $validation_errors = Validator::make($inputs, $rules);
        if ($validation_errors->fails())
        {
            return response()->json([
                'status' => 'error validation',
                'message' => $validation_errors->errors()->all(),
            ]);
        }
        else
        {
            $status = Permission::create($inputs);
            if ($status)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'permission has been created successfully'
                ]);
            }
            else
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'permission has not been created'
                ]);
            }
        }
    }
    public function show($id)
    {
        try {
            $permission = Permission::findById($id);
            if ($permission)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'permission has been found',
                    'data' => $permission
                ]);
            }
            else
            {
                return response()->json([
                    'message' => 'permission has not been found',
                    'status' => 'error'
                ]);
            }
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        $permission = Permission::findById($id);
        $status = $permission->delete();
        if ($status)
        {
            return response()->json([
                'status' => 'success',
                'message' => 'permission has been deleted'
            ]);
        }
        else
        {
            return response()->json([
                'status' => 'error',
                'message' => 'permission has not deleted'
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'name' => 'required|string'
            ];
            $inputs = $request->only('name');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails())
            {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ]);
            }
            else
            {
                $permission = Permission::findById($id);
                if ($permission)
                {
                    $status = $permission->fill([
                        'name' => $request->input('name')
                    ])->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'permission has been updated successfully'
                    ]);
                }
                else
                {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'permission has not been updated'
                    ]);
                }
            }
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


}
