@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-custom-orange focus:ring-custom-orange rounded-md shadow-sm']) !!}>
