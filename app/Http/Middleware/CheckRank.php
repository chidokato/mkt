<?php 
public function handle($request, \Closure $next, ...$ranks)
{
    $user = auth()->user();
    if (!$user || !in_array((string)$user->rank, $ranks, true)) {
        abort(403);
    }
    return $next($request);
}

?>