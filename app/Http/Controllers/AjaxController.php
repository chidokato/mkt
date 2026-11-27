<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
// use Request;
use Session;
use Illuminate\Http\Request;
use Image;
use File;
use App\Models\Category;
use App\Models\Option;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Images;
use App\Models\Section;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\Street;


class AjaxController extends Controller
{
    public function change_province($id)
    {
        $data = District::where('province_id',$id)->get();
        echo '<option value="">...</option>';
        foreach ($data as $key => $value) {
    		echo '<option value="'.$value->id.'">'.$value->name.'</option>';
        }
    }
    public function change_district($id)
    {
        $data = Ward::where('district_id',$id)->get();
        echo '<option value="">...</option>';
        foreach ($data as $key => $value) {
            echo '<option value="'.$value->id.'">'.$value->name.'</option>';
        }
    }
    public function change_district_street($id)
    { 
        $data = Street::where('district_id',$id)->get();
        echo '<option value="">...</option>';
        foreach ($data as $key => $value) {
            echo '<option value="'.$value->id.'">'.$value->name.'</option>';
        }
    }
    public function change_ward_lang($id)
    { 
        $data = WardTranslation::where('ward_id',$id)->get();
        foreach ($data as $key => $value) {
            echo '<input value="'.$value->id.'" name="ward_id:'.$value->locale.'" type="hidden">';
        }
    }

    public function change_SortBy($id)
    {
        $data = Category::where('sort_by',$id)->get();
        return view('admin.category.listparent',['data'=>$data]);
    }

    public function change_parent($id)
    {
        $locale = Session::get('locale');
        $data = CategoryTranslation::where('category_id',$id)->get();
        foreach ($data as $key => $value) {
            echo '<input value="'.$value->id.'" type="hidden" name="category:'.$value->locale.'">';
        }
    }

    public function update_category_view($id,$view)
    {
        $data = Category::find($id);
        $data->view = $view;
        $data->save();
    }

    public function update_menu_view($id,$view)
    {
        $data = Menu::find($id);
        $data->view = $view;
        $data->save();
    }

    public function del_img_detail($id)
    {
        $data = Images::find($id);
        if(File::exists('data/images/'.$data->img)) { File::delete('data/images/'.$data->img);} // xóa ảnh cũ
        $data->delete();
    }

    public function name_img_detail($id, $name)
    {
        $data = Images::find($id);
        $data->name = $name;
        $data->save();
    }

    public function del_section($id)
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        foreach ($section->Images as $image) {
            if (File::exists('data/images/'.$image->img)) {
                File::delete('data/images/'.$image->img);
            }

            $image->delete();
        }

        $section->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function update_status_category($id, $status)
    {
        $category = Category::find($id);
        $category->status = $status;
        $category->save();
    }

    public function update_status_post($id, $status)
    {
        $Post = Post::find($id);
        $Post->status = $status;
        $Post->save();
    }

    public function update_status_province($id, $status)
    {
        $province = Province::find($id);
        $province->status = $status;
        $province->save();
    }

    public function update_home_province($id, $status)
    {
        $province = Province::find($id);
        $province->home = $status;
        $province->save();
    }

    public function update_hot_post($id, $hot)
    {
        $Post = Post::find($id);
        $Post->hot = $hot;
        $Post->save();
    }

    public function change_category($id)
    {
        $data = Option::where('category_id',$id)->where('parent',0)->get();
        return view('admin.option.listoption',['data'=>$data]);
    }

    public function change_arrange_mat($id)
    {
        if ($id=='new') {
            $mat = Post::where('category_id',75)->orderBy('id', 'desc')->get();
        }else{
            $mat = Post::where('category_id',75)->orderBy('price', $id)->get();
        }
        return view('pages.iteam.load_knot',['mat'=>$mat]);
    }
    public function change_arrange_day($id)
    {
        if ($id=='new') {
            $day = Post::where('category_id',76)->orderBy('id', 'desc')->get();
        }else{
            $day = Post::where('category_id',76)->orderBy('price', $id)->get();
        }
        return view('pages.iteam.load_knot',['day'=>$day]);
    }
    public function change_arrange_khoa($id)
    {
        if ($id=='new') {
            $khoa = Post::where('category_id',90)->orderBy('id', 'desc')->get();
        }else{
            $khoa = Post::where('category_id',90)->orderBy('price', $id)->get();
        }
        return view('pages.iteam.load_knot',['khoa'=>$khoa]);
    }

    public function change_arrange_cat($id,$catid)
    {
        // cat_array
        $cat_array = [$catid];
        $cates = Category::where('parent', $catid)->get();
        foreach ($cates as $key => $cate) {
            $cat_array[] = $cate->id;
        }
        // cat_array
        
        if ($id=='new') {
            $post = Post::whereIn('category_id', $cat_array)->orderBy('id', 'desc')->get();
        }else{
            $post = Post::whereIn('category_id', $cat_array)->orderBy('price', $id)->get();
        }
        return view('pages.iteam.load_cat',['post'=>$post]);
    }
}
