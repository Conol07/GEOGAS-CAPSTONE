@props(['status'])
@php
    $labels = [
        'under_review' => 'Under Review',
        'resolved' => 'Resolved',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp
<span class="status-badge status-{{ $status }}">{{ $label }}</span>
