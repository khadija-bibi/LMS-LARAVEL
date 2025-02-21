<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('name','DESC')->paginate(25);
        return view('roles.list',[
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name','ASC')->get();
        return view('roles.create',[
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=> 'required|unique:roles|min:5'
        ]);

        if ($validator->passes()) {
            $role = Role::Create(['name'=> $request->name]);
            if (!empty($request->permission)) {
                foreach ($request-> permission as $name) {
                    $role->givePermissionTo($name);
                }
            }

            return redirect()->route('roles.index')->with('success','Role added successfully');
        }
        else{
            return redirect()->route('roles.create')->withInput()->withErrors($validator);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permissions = Permission::orderBy('name','ASC')->get();

        $role = Role::findOrFail($id);
        $hasPermissions = $role->permissions->pluck('name');
        return view('roles.edit', compact('permissions', 'role', 'hasPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'name'=> 'required|unique:roles,name,'.$id.',id|min:5'
        ]);

        if ($validator->passes()) {
            $role->name = $request->name;
            $role->save();
            if (!empty($request->permission)) {
                $role->syncPermissions($request->permission);  
            }
            else{
                $role->syncPermissions([]);  

            }

            return redirect()->route('roles.index')->with('success','Role added successfully');
        }
        else{
            return redirect()->route('roles.edit',$id)->withInput()->withErrors($validator);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Role::findOrFail(decrypt($id))->delete();
        session()->flash('success','Role deleted successfully');
        return redirect()->back();
    }
}
