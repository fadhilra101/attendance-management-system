@props(['options', 'name', 'value' => null])

<select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100']) }}>
    <option value="">{{ __('Select an option') }}</option>
    @foreach($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" {{ $optionValue == $value ? 'selected' : '' }}>
            {{ $optionLabel }}
        </option>
    @endforeach
</select>
