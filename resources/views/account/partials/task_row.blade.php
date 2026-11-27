<tr>
    <td>{{ $val->User?->yourname }}</td>

    <td>
        {{ $val->User?->department?->hierarchy_levels['level3'] ?? '-' }}
        <br>
        <small>{{ $val->User?->department?->hierarchy_levels['level2'] ?? '-' }}</small>
    </td>

    <td>{{ $val->Post?->name }}</td>
    <td>{{ $val->Channel?->name }}</td>

    <td>{{ number_format($val->expected_costs, 0, ',', '.') }}đ</td>

    <td>{{ $days }}</td>

    <td>{{ number_format($val->total_costs ?? $days * $val->expected_costs, 0, ',', '.') }} đ</td>

    <td>{{ number_format($val->support_money ?? 0, 0, ',', '.') }}đ</td>

    <td>{{ $val->content }}</td>

    <td>{{ $val->kpi ?? '-' }}</td>

    <td>
        <label class="switch">
            <input type="checkbox" class="active-toggle"
                data-id="{{ $val->id }}"
                {{ $val->approved ? 'checked' : '' }}>
            <span class="slider round"></span>
        </label>
    </td>

    <td>
        @if($val->approved)
            <span class="badge bg-success">Đã duyệt</span>
        @else
            <span class="badge bg-warning">Chờ duyệt</span>
        @endif
    </td>
</tr>
