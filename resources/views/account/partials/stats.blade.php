
    <div class="mb-3 thongke widget-list">
        <h4><span>Thống kê</span></h4>
        <ul>
            <li class="mb-3">
                <div><span>Tổng số:</span> 
                    <span id="tongduan">{{ $tasks->pluck('post_id')->unique()->count() }} dự án</span>
                </div>
            </li>
            <li class="mb-3">
                <div><span>Tổng tiền:</span> 
                    <span id="tongtien">{{ number_format($total_expected, 0, ',', '.') }} đ</span>
                </div>
            </li>
            <li class="mb-3">
                <div><span>Tổng tiền phải nộp (dự kiến):</span> 
                    <span id="tongphainop" class="required">{{ number_format($total_pay, 0, ',', '.') }} đ</span>
                </div>
            </li>
        </ul>
    </div>

