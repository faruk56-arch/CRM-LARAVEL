@php ($arr_name = ['firstname', 'lastname', 'name', 'full-name', 'prenom-et-nom'])
@php ($arr_contact = ['email', 'phone-number', 'post-code', 'phone', 'zip', 'address', 'city', 'country', 'tel', 'code-postal', 'numero-de-telephone'])
@php ($arr_utm = ['utm_source', 'utm_medium', 'utm_campaign'])
@php ($arr_additional = ['id', 'created'])
@php ($arr = array_merge($arr_name, $arr_contact, $arr_utm, $arr_additional))

@foreach($leads as $key => $lead)

<div style="height: 900px">
    <h1>{{ $key + 1 }}.
        @if (isset($lead['name']))
            {{ ucfirst($lead['name'])}}
        @elseif (isset($lead['full-name']))
            {{ ucfirst($lead['full-name']) }}
        @elseif (isset($lead['prenom-et-nom']))
            {{ ucfirst($lead['prenom-et-nom']) }}
        @elseif (isset($lead['lastname']))
            {{ ucfirst($lead['firstname']) }} {{ ucfirst($lead['lastname']) }}
        @else
            {{ ucfirst($lead['firstname']) }}
        @endif
    </h1>

    <hr>
    <h4>Export du {{ $date }} - {{ count($leads) }} FICHES</h4>
    <h5>ID: {{ $lead['id'] }}</h5>
    <hr>
    <table>
        @foreach($lead as $key => $data)
            @if (!in_array($key, $arr))
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $data }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <hr>
    <table>
        @foreach($lead as $key => $data)
            @if (in_array($key, $arr_contact))
                <tr>
                    <td>{{ $key }}</td>
                    <td>
                        @if ($key == 'email')
                            <a href="mailto:{{ $data }}">{{ $data }}</a>
                        @elseif ($key == 'phone' || $key == 'phone-number' || $key == 'tel')
                            <a href="tel:{{ $data }}">{{ $data }}</a>
                        @else
                            {{ $data }}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </table>

    <hr>
    <table>
        @foreach($lead as $key => $data)
            @if (in_array($key, $arr_utm) && !empty($data))
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $data }}</td>
                </tr>
            @endif
        @endforeach
    </table>
</div>
@endforeach

<style>
    @font-face {
        font-family: 'Roboto';
        src: url({{ storage_path('fonts\Roboto-Regular.ttf') }}) format("truetype");
        font-weight: 400;
        font-style: normal;
    }

    div {
        font-family: 'Roboto', sans-serif;
    }

    hr {
        margin: 20px 0;
    }

    h4 {
        margin-bottom: 10px;
        margin-top: 0;
        font-size: 25px;
    }

    h5 {
        color: #2a9055;
        margin-top: 10px;
        margin-bottom: 0;
        font-size: 18px;
    }

    tr td:first-child {
        width: 200px;
        font-weight: bold;
        text-transform: capitalize;
    }
</style>
