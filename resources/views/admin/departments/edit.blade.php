@extends('account.layout.index')

@section('content')
@include('admin.alert')

<form method="post" action="{{route('departments.update', $department)}}" enctype="multipart/form-data">
@csrf
@method('PUT')
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow fixed">
    <button type="button" id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
    <ul class="navbar-nav ">
        <li class="nav-item"> <a class="nav-link line-1" href="{{route('departments.index')}}" ><i class="fa fa-chevron-left" aria-hidden="true"></i> <span class="mobile-hide">Quay lại</span> </a> </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mobile-hide">
            <button type="reset" class="btn-danger mr-2 form-control"><i class="fas fa-sync"></i> Làm mới</button>
        </li>
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item">
            <button type="submit" class="btn-success form-control"><i class="far fa-save"></i> Lưu lại</button>
        </li>
    </ul>
</nav>

<div class="d-sm-flex align-items-center justify-content-between mb-3 flex" style="height: 38px;">
    <h2 class="h3 mb-0 text-gray-800 line-1 size-1-3-rem">Thêm mới</h2>
</div>

<div class="row">
  <div class="col-xl-9 col-lg-9">
        <div class="card shadow mb-2">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <ul class="nav nav-pills">
                    <li><a data-toggle="tab" class="nav-link " href="#en">Nội dung</a></li>
                </ul>
            </div>
            <div class="tab-content overflow">
                <div class="tab-pane active" id="en">
                  <div class="card-body">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label>Name</label>
                                  <input value="{{$department->name}}" name="name" placeholder="..." type="text" class="form-control">
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label>Mã phòng</label>
                                  <input value="{{ $department->code }}" name="code" placeholder="..." type="text" class="form-control">
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label class="">Root</label>
                                  <select name="parent" class="form-control select2">
                                    <option value="0">-- ROOT --</option>
                                    {!! $options !!}
                                </select>
                              </div>
                          </div>
                          
                          
                      </div>
                  </div>
                </div>
            </div>
            
        </div>
    </div>
    <div class="col-xl-3 col-lg-3">
   
        
    </div>
</div>
</form>

@endsection