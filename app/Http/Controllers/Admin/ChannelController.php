<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TreeHelper;

class ChannelController extends Controller
{
    public function index()
    {
        $channels = Channel::with('user')->orderBy('parent')->get();
        return view('admin.channels.index', compact('channels'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        $channels = Channel::all();
        return view('admin.channels.create', compact('users','channels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'parent' => 'nullable|exists:channels,id',
        ]);

        $code = $request->code ?: 'CH-' . strtoupper(substr(uniqid(), -6));

        Channel::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'user_id' => Auth::user()->id,
            'parent' => $request->parent && $request->parent != 0 ? $request->parent : 0,
        ]);

        return redirect()->route('channels.index')->with('success', 'Thêm channel thành công');
    }

    public function edit($id)
    {
        $channel = Channel::findOrFail($id);
        $items = Channel::all();

        $options = TreeHelper::buildOptions(
            items: $items,
            parentId: 0,
            prefix: '',
            selectedId: $channel->parent, 
            idField: 'id',
            parentField: 'parent',
            nameField: 'name'
        );

        return view('admin.channels.edit', compact('channel', 'options'));
    }

    public function update(Request $request, Channel $channel)
    {
        $request->validate([
            'name' => 'required',
            // 'parent' => 'nullable|exists:channels,id',
        ]);

        $channel->update([
            'name' => $request->name,
            'code' => $request->code ?: 'CH-' . strtoupper(substr(uniqid(), -6)),
            'description' => $request->description,
            'user_id' => Auth::user()->id,
            'parent' => $request->parent && $request->parent != 0 ? $request->parent : 0,
        ]);

        return redirect()->route('channels.index')->with('success', 'Cập nhật thành công!');
    }

    public function updateName(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $channel = Channel::findOrFail($id);
        $channel->update(['name' => $request->name]);

        // return response()->json(['success' => true, 'name' => $channel->name]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!' ]);
    }

    public function destroy(Channel $channel)
    {
        $channel->delete();
        return redirect()->route('channels.index')->with('success','Xóa thành công');
    }

    // Nhân bản
    public function duplicate($id)
    {
        $channel = Channel::findOrFail($id);
        $new = $channel->replicate();
        $new->code = 'CH-' . strtoupper(substr(uniqid(), -6));
        $new->save();

        return redirect()->route('channels.index')->with('success','Nhân bản thành công');
    }
}
