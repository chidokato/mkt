@extends('account.layout.index')

@section('content')
<div class="container">
    <h3>Gửi mail hàng loạt</h3>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div><b>Lỗi:</b></div>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        Tổng user có email trong DB: <b>{{ number_format($count) }}</b><br>
        Hỗ trợ cá nhân hóa trong nội dung: <code>{name}</code> và <code>{email}</code>.
    </div>

    <form method="POST" action="{{ route('admin.bulk_mail.send') }}">
        @csrf

        <div class="form-group mb-3">
            <label>Tiêu đề (Subject)</label>
            <input type="text" name="subject" class="form-control"
                   value="{{ old('subject', 'Thông báo') }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Nội dung</label>
            <textarea name="content" rows="10" class="form-control" required>{{ old('content', "Kính gửi Anh/Chị,\n\nPhòng Marketing đã mở link đăng ký cho kỳ 1 tháng 5. Hệ thống sẽ đóng đăng ký vào ngày 30/04.\n\nĐề nghị Anh/Chị vui lòng hoàn tất đăng ký trước thời hạn trên để tránh trường hợp đăng ký muộn và không được phê duyệt.\n\nTrân trọng.") }}</textarea>
            <small class="text-muted">Ví dụ: "Chào {name}" sẽ tự thay theo từng người nhận.</small>
        </div>

        <div class="card mb-3">
            <div class="card-header"><b>Chọn người nhận</b></div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="send_all" value="1" id="send_all" {{ old('send_all') ? 'checked' : '' }}>
                    <label class="form-check-label" for="send_all">
                        Gửi cho tất cả user có email
                    </label>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Chọn theo phòng ban (có thể chọn nhiều)</label>
                        <select id="department_ids" name="department_ids[]" class="form-control" multiple size="10">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ collect(old('department_ids', []))->contains($department->id) ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Chọn phòng ban để gửi cho toàn bộ user thuộc phòng đó.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Chọn từng email (có thể chọn nhiều)</label>
                        <select id="user_ids" name="user_ids[]" class="form-control" multiple size="10">
                            @foreach($users as $u)
                                @php
                                    $displayName = $u->yourname ?: ($u->name ?: 'N/A');
                                    $deptName = optional($u->department)->name;
                                @endphp
                                <option value="{{ $u->id }}"
                                    {{ collect(old('user_ids', []))->contains($u->id) ? 'selected' : '' }}>
                                    {{ $displayName }} - {{ $u->email }}{{ $deptName ? ' - ' . $deptName : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Bạn có thể chọn một vài người riêng lẻ.</small>
                    </div>
                </div>

                <small class="text-muted">
                    Quy tắc chọn người nhận: nếu tick "tất cả" thì gửi toàn bộ; nếu không thì lấy hợp nhất danh sách chọn theo phòng ban + theo từng email.
                </small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label>Nghỉ giữa mỗi email (giây, tùy chọn)</label>
                <input type="number" name="seconds_per_email" class="form-control"
                       value="{{ old('seconds_per_email', 0) }}" min="0" max="60">
            </div>

            <div class="col-md-3 mb-3 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="only_verified" value="1"
                           id="only_verified" {{ old('only_verified') ? 'checked' : '' }}>
                    <label class="form-check-label" for="only_verified">
                        Chỉ gửi email đã verify
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <button class="btn btn-primary" type="submit"
                    onclick="return confirm('Xác nhận gửi mail ngay bây giờ?');">
                Bấm gửi
            </button>

            <a href="{{ url('/') }}" class="btn btn-secondary">Quay lại</a>
        </div>

        <div class="alert alert-warning mb-0">
            <b>Luu y:</b> He thong se xep hang gui nen khi bam gui. Neu danh sach lon, hay dam bao queue worker dang chay.
        </div>
    </form>

    @if(!empty($batchId))
        <hr>
        <h5>Trạng thái gửi (Batch: <code>{{ $batchId }}</code>)</h5>

            <div class="mb-3">
                <span class="badge bg-secondary">Queued: <span id="stat-queued">{{ $stats['queued'] }}</span></span>
                <span class="badge bg-success">Sent: <span id="stat-sent">{{ $stats['sent'] }}</span></span>
                <span class="badge bg-danger">Failed: <span id="stat-failed">{{ $stats['failed'] }}</span></span>
                <a class="btn btn-sm btn-outline-primary ms-2" href="{{ route('admin.bulk_mail.create', ['batch_id' => $batchId]) }}">
                    Refresh
                </a>
            </div>

            @if($stats['queued'] > 0)
            <div class="progress mb-3" style="height: 25px;">
                @php
                    $total = $stats['queued'] + $stats['sent'] + $stats['failed'];
                    $percent = $total > 0 ? floor((($stats['sent'] + $stats['failed']) / $total) * 100) : 0;
                @endphp
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
            </div>
            <div class="alert alert-info" id="processing-alert">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Đang tiến hành gửi ngầm... Vui lòng <b>KHÔNG</b> đóng tab này cho đến khi hoàn thành (Queued = 0).
            </div>
            @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Gửi lúc</th>
                        <th>Lỗi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->email }}</td>
                            <td>
                                @if($row->status === 'sent')
                                    <span class="badge bg-success">sent</span>
                                @elseif($row->status === 'failed')
                                    <span class="badge bg-danger">failed</span>
                                @else
                                    <span class="badge bg-secondary">queued</span>
                                @endif
                            </td>
                            <td>{{ optional($row->sent_at)->format('Y-m-d H:i:s') }}</td>
                            <td style="max-width: 420px; white-space: normal;">
                                @if($row->status === 'failed')
                                    <small class="text-danger">{{ $row->error }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Chưa có log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($logs, 'links'))
            <div class="mt-2">
                {{ $logs->links() }}
            </div>
        @endif
    @endif
</div>

<script>
    (function () {
        const sendAll = document.getElementById('send_all');
        const departmentSelect = document.getElementById('department_ids');
        const userSelect = document.getElementById('user_ids');

        function toggleRecipientSelectors() {
            const disabled = !!sendAll.checked;
            departmentSelect.disabled = disabled;
            userSelect.disabled = disabled;
        }

        sendAll.addEventListener('change', toggleRecipientSelectors);
        toggleRecipientSelectors();
    })();

    @if(!empty($batchId) && $stats && $stats['queued'] > 0)
    (function () {
        let batchId = '{{ $batchId }}';
        let processing = false;
        let limit = 10;
        
        function processNextChunk() {
            if (processing) return;
            processing = true;
            
            fetch('{{ route('admin.bulk_mail.process_chunk') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    batch_id: batchId,
                    limit: limit
                })
            })
            .then(r => r.json())
            .then(data => {
                processing = false;
                if (data.error) {
                    alert('Lỗi: ' + data.error);
                    return;
                }
                
                let stats = data.stats;
                let total = stats.queued + stats.sent + stats.failed;
                let percent = total > 0 ? Math.floor(((stats.sent + stats.failed) / total) * 100) : 0;
                
                document.getElementById('stat-queued').innerText = stats.queued;
                document.getElementById('stat-sent').innerText = stats.sent;
                document.getElementById('stat-failed').innerText = stats.failed;
                
                let pb = document.getElementById('progress-bar');
                if (pb) {
                    pb.style.width = percent + '%';
                    pb.innerText = percent + '%';
                }
                
                if (stats.queued > 0) {
                    // Nghỉ 1 giây giữa các chunk để tránh bị block IP hoặc quá tải CPU
                    setTimeout(processNextChunk, 1000);
                } else {
                    document.getElementById('processing-alert').className = 'alert alert-success';
                    document.getElementById('processing-alert').innerHTML = '<b>Hoàn thành!</b> Đã gửi xong toàn bộ email trong batch này.';
                    setTimeout(() => window.location.reload(), 2000);
                }
            })
            .catch(e => {
                processing = false;
                console.error(e);
                // Nếu lỗi mạng, thử lại sau 5 giây
                setTimeout(processNextChunk, 5000);
            });
        }
        
        // Bắt đầu xử lý sau 1 giây
        setTimeout(processNextChunk, 1000);
    })();
    @endif
</script>
@endsection
