@props(['headers' => [], 'rows' => [], 'emptyMessage' => 'No se encontraron registros', 'sortable' => false])

<div class="table-responsive">
    <table class="data-table" role="table">
        <thead>
            <tr>
                @foreach($headers as $index => $header)
                    <th scope="col" class="{{ $header['align'] ?? '' === 'right' ? 'table-th-right' : '' }} {{ ($header['align'] ?? '') === 'center' ? 'table-th-center' : '' }}" @if($sortable) data-sort="{{ $index }}" @endif>
                        {{ $header['label'] ?? $header }}
                        @if($sortable)
                            <span class="sort-indicator" aria-hidden="true"></span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr class="{{ isset($row['annulled']) && $row['annulled'] ? 'row--annulled' : '' }}">
                    @foreach($row as $cell)
                        <td class="{{ ($cell['align'] ?? '') === 'right' ? 'table-td-right' : '' }} {{ ($cell['align'] ?? '') === 'center' ? 'table-td-center' : '' }} {{ $cell['class'] ?? '' }}">
                            {!! $cell['value'] ?? $cell !!}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="table-empty">
                        <div class="table-empty-icon" aria-hidden="true">&#x1F4CB;</div>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
