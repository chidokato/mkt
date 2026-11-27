@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')

@endsection

@section('body') @endsection

@section('content')

<div class="content container-fluid">

  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm mb-2 mb-sm-0">
        <h1 class="page-header-title">TẠO THƯ MỜI ONLINE</h1>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-3 mb-3 ">
      <div class="card">
        <div class="card-body">
          @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
          </div>
          @endif
          <form id="inviteForm" method="POST" action="{{ route('invite.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label class="form-label">Ảnh avatar</label>
              <input type="file" name="avatar" class="form-control" accept="image/*" required>
              <small class="text-muted">PNG/JPG/WebP, tối đa 4MB</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Họ và tên</label>
              <div class="input-group">
                <select name="gender" class="form-control" style="max-width:120px">
                  <option value="MR">MR</option>
                  <option value="MS">MS</option>
                </select>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Chức vụ</label>
              <input type="text" name="title" class="form-control" value="{{ old('title') }}">
            </div>

            <button class="btn btn-primary">Tạo ảnh & tải về !</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-sm-3 mb-3">
      <div class="w-100 img-result"><img class="w-100" src="templates/anhmau.jpg"></div>
    </div>

    <div class="col-sm-6 mb-3">
      <div>
        <img class="w-100" src="templates/Timeline.jpg">
      </div>
    </div>

  </div>
  
</div>

@endsection


@section('js')

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('inviteForm');
  const resultBox = document.querySelector('.img-result');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    resultBox.innerHTML = `<div class="alert alert-info mb-2">Đang tạo ảnh...</div>`;
    const fd = new FormData(form);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json', // QUAN TRỌNG
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: fd
      });

      const contentType = res.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await res.text();
        console.log('NON-JSON RESPONSE:', text);
        resultBox.innerHTML = `<div class="alert alert-danger">Server trả HTML (không phải JSON). Xem Console.</div>`;
        return;
      }

      const data = await res.json();

      if (!res.ok) {
        const msg = data.message || 'Tạo ảnh thất bại';
        const errors = data.errors
          ? Object.values(data.errors).flat().join('<br>')
          : '';
        resultBox.innerHTML = `<div class="alert alert-danger"><b>${msg}</b><br>${errors}</div>`;
        return;
      }

      if (!data.ok) {
        resultBox.innerHTML = `<div class="alert alert-danger">Tạo ảnh thất bại (không có ok:true).</div>`;
        console.log('DATA:', data);
        return;
      }

      const imgUrl = data.image_url + '?t=' + Date.now();
      resultBox.innerHTML = `
        <img class="w-100 mb-2" src="${imgUrl}" alt="Invite result">
        <a class="btn btn-success w-100" href="${data.download_url}">Tải ảnh về</a>
      `;
    } catch (err) {
      console.error(err);
      resultBox.innerHTML = `<div class="alert alert-danger">Lỗi kết nối hoặc server (xem Console/Log).</div>`;
    }
  });
});
</script>

@endsection