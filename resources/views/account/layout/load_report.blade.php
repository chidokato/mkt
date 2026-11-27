<div class="table-responsive">
    <table class="table table-thead-bordered table-nowrap table-align-middle card-table no-footer">
        <thead class="thead-light">
            <tr>
                 
                <th></th>
                <th>Thời gian</th>
                <th>Tổng tiền (duyệt)</th>
                <th>Thực tế</th>
                <th>Số ngày</th>
                <th>Đóng/mở</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $r)
            <tr data-id="{{ $r->id }}">
                <td>
                    <a class="btn btn-sm btn-success" href="report/{{ $r->id }}">Duyệt MKT</a>
                </td>
                <td><strong> <span class="r-date">{{ \Carbon\Carbon::parse($r->time_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($r->time_end)->format('d/m/Y') }}</span></strong> <br> <small class="r-name">{{ $r->name }}</small> </td>
                <td class="js-expected-cell">
                  
                  <span class="js-expected-text">{{ number_format((int)$r->expected_costs, 0, ',', '.') }}</span>
                  <a href="javascript:void(0)" class="js-refresh-expected" data-id="{{ $r->id }}" title="Cập nhật tổng dự kiến">
                    <i class="tio-refresh"></i>
                  </a>
                </td>

                <td class="js-actual-cell">
                  
                  <span class="js-actual-text">{{ number_format((int)$r->actual_costs, 0, ',', '.') }}</span>
                  <a href="javascript:void(0)" class="js-refresh-actual" data-id="{{ $r->id }}" title="Cập nhật tổng thực tế">
                    <i class="tio-refresh"></i>
                  </a>
                </td>
                <td>{{ $r->days }}</td>
                <td>
                    <label class="toggle-switch toggle-switch-sm" for="checkbox{{ $r->id }}">
                        <input type="checkbox" class="toggle-switch-input active-toggle" id="checkbox{{ $r->id }}" data-id="{{ $r->id }}" {{ $r->active ? 'checked' : '' }}>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </td>
                <td>
                    <div class="btn-group" role="group">
                      <a class="btn btn-sm btn-white edit">
                        <i class="tio-edit"></i> Edit
                      </a>
                      <a class="btn btn-sm btn-white del {{ (int) $r->tasks_count > 0 ? 'disabled' : '' }}" data-id="{{ $r->id }}" data-has-tasks="{{ (int) $r->tasks_count > 0 ? 1 : 0 }}" title="{{ (int) $r->tasks_count > 0 ? 'Bao cao da co tac vu, khong the xoa' : 'Xoa bao cao' }}" aria-disabled="{{ (int) $r->tasks_count > 0 ? 'true' : 'false' }}">
                        <i class="tio-delete"></i>
                      </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


