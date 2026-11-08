@php($u = $person->user)
@if($u)
<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
    <div>
        <div class="fw-semibold small">{{ $u->name }}</div>
        <div class="text-muted-gg" style="font-size:.72rem;">{{ $u->email }}@if($u->phone) &middot; {{ $u->phone }}@endif</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <x-status-badge :status="$u->status" />
        <form method="POST" action="{{ route('lgu.users.toggle', $u) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-outline-secondary">{{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
        </form>
    </div>
</div>
@endif
