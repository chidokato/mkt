@forelse($transactions as $t)
  <tr>
    <td>{{ $t->created_at }}</td>
    <td>{{ strtoupper($t->type ?? '---') }}</td>
    <td>{{ number_format($t->amount ?? 0) }} đ</td>
    <td>{{ $t->description ?? '---' }}</td>
  </tr>
@empty
  <tr>
    <td colspan="4" class="text-center text-muted">Chưa có lịch sử</td>
  </tr>
@endforelse
