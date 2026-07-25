<x-screen.admin-filters
    :regions="$regions ?? []"
    :agents="$agents ?? []"
    :types="$types ?? []"
    :period="$period ?? 'month'"
    :date="request('date', now()->format('Y-m-d'))"
    :currentFilters="request()->only(['date_from', 'date_to', 'include_annulled'])"
/>
