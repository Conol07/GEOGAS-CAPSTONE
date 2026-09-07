@props(['price'])
@php $direction = $price->priceChangeDirection(); @endphp
@if($direction === 'increased')
    <span class="text-danger small"><i class="bi bi-arrow-up-short"></i> Increased</span>
@elseif($direction === 'decreased')
    <span style="color:#15803D;" class="small"><i class="bi bi-arrow-down-short"></i> Decreased</span>
@elseif($direction === 'unchanged')
    <span class="text-muted-gg small"><i class="bi bi-dash"></i> No Change</span>
@endif
