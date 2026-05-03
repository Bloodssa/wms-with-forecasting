import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export default function useSearchFilter(routeName, initFilters = {}, debounceMs = 400) {
    const filters = ref({
        search: initFilters.search || '',
        ...initFilters
    });
    let timeout = null; // init debounce

    // debounce search
    watch(() => filters.value.search, (value) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            router.get(route(routeName), filters.value, {
                preserveState: true,
                replace: true,
            });
        }, debounceMs);
    });

    // auto search no debounce for dropdown
    watch(filters, (value, oldValue) => {
        router.get(route(routeName), value, {
            preserveState: true,
            replace: true,
        });
    }, { deep: true });

    return { filters };
}