<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Image;
use File;

use App\Models\Category;
use App\Models\Post;

use Carbon\Carbon;
use App\Helpers\SimpleXLSX; // Import thư viện SimpleXLSX


class DuanController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::where('sort_by', 'Product')->orderBy('rate', 'DESC')->paginate(100);
        return view('admin.duan.index', compact(
            'posts',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:posts,name',
        ], [
            'name.unique' => 'Tên dự án đã tồn tại!',
        ]);
                
        $data = $request->all();
        $post = new Post();
        $post->user_id = Auth::User()->id;
        $post->status = 'false';
        $post->sort_by = 'Product';
        $post->name = $data['name'];
        $post->rate = $data['rate'];
        $post->slug = Str::slug($data['name'], '-');

        $post->save();
        return redirect('admin/duan')->with('success','updated successfully');
    }

    public function updateRate(Request $request)
    {
        $post = Post::find($request->id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy bài viết']);
        }

        $post->rate = $request->rate;
        $post->save();

        return response()->json(['status' => true, 'message' => 'Đã lưu thành công']);
    }

    public function updateName(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $post = Post::findOrFail($id);
        $post->update(['name' => $request->name]);

        // return response()->json(['success' => true, 'name' => $channel->name]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!' ]);
    }

}
