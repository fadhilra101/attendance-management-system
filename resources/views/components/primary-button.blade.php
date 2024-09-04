<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:bg-gray-600 disabled:dark:bg-gray-400 disabled:text-gray-300 disabled:dark:text-gray-600 disabled:opacity-25 disabled:dark:opacity-25 disabled:hover:bg-gray-600 disabled:dark:hover:bg-gray-400 disabled:focus:bg-gray-600 disabled:dark:focus:bg-gray-400 disabled:active:bg-gray-900 disabled:dark:active:bg-gray-300 disabled:focus:ring-offset-gray-800 disabled:dark:focus:ring-offset-gray-800 disabled:cursor-not-allowed'
    ]) }}
>
    {{ $slot }}
</button>
