<table>
    <thead>
    <tr>
        @foreach($keys as $key)
            <th style="background-color: blue">{{ $key }}</th>
        @endforeach

    </tr>
    </thead>
    <tbody>
    @foreach($leads as $lead)
        <tr>
            @foreach($keys as $key)
                @if (isset($lead[$key]))
                <td>{{ $lead[$key] }}</td>
                @else
                    <td></td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<style>
    td, th, tr {
        width: auto !important;
    }
</style>
