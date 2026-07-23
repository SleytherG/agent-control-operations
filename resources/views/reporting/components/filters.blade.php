<x-screen.admin-filters
    :regions="$regions ?? []"
    :stores="$stores ?? []"
    :banks="$banks ?? []"
    :bankAgents="$bankAgents ?? []"
    :types="$types ?? []"
    :provinces="$provinces ?? []"
    :districts="$districts ?? []"
    :operators="$operators ?? []"
    :period="$period ?? 'month'"
    :date="request('date', now()->format('Y-m-d'))"
    :currentFilters="request()->only(['province_id', 'district_id', 'date_from', 'date_to', 'include_annulled'])"
/>
