<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TreeHelper;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('user')->orderBy('parent')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        $suppliers = Supplier::all();
        return view('admin.suppliers.create', compact('users','suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'user_id' => 'nullable|exists:users,id',
            // 'parent' => 'nullable|exists:suppliers,id',
        ]);

        $code = $request->code ?: 'SUP-' . strtoupper(substr(uniqid(), -6));

        Supplier::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'user_id' => Auth::User()->id,
            'parent' => $request->parent,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Thêm nhà cung cấp thành công');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $items = Supplier::all();

        $options = TreeHelper::buildOptions(
            items: $items,
            parentId: 0,
            prefix: '',
            selectedId: $supplier->parent, 
            idField: 'id',
            parentField: 'parent',
            nameField: 'name'
        );

        return view('admin.suppliers.edit', compact('supplier', 'options'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required',
            // 'parent' => 'exists:suppliers,id',
        ]);

        $supplier->update([
            'name' => $request->name,
            'code' => $request->code ?: 'SUP-' . strtoupper(substr(uniqid(), -6)),
            'description' => $request->description,
            'user_id' => Auth::User()->id,
            'parent' => $request->parent,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Cập nhật thành công');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Xóa thành công');
    }

    // Hàm nhân bản
    public function duplicate($id)
    {
        $supplier = Supplier::findOrFail($id);
        $new = $supplier->replicate();
        $new->code = 'SUP-' . strtoupper(substr(uniqid(), -6));
        $new->save();

        return redirect()->route('suppliers.index')->with('success','Nhân bản thành công');
    }
}
