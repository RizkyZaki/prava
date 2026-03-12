<div class="me-2 flex items-center">
    <span class="inline-flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 text-xs font-semibold leading-none">
        <a href="{{ route('locale.set', 'id') }}"
           @class([
               'px-2.5 py-1.5 transition-colors duration-150',
               'bg-primary-500 text-white' => app()->getLocale() === 'id',
               'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' => app()->getLocale() !== 'id',
           ])>ID</a>
        <a href="{{ route('locale.set', 'en') }}"
           @class([
               'px-2.5 py-1.5 transition-colors duration-150',
               'bg-primary-500 text-white' => app()->getLocale() === 'en',
               'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' => app()->getLocale() !== 'en',
           ])>EN</a>
    </span>
</div>
