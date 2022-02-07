<html>
    <body>
    <h3>Rapport journalier</h3>
    <h5>Rapport journalier du {{ $date }}</h5>
    <br>
    <table>
        <tr>
            <td>Landing/Formulaire</td>
            <td>Type</td>
            <td>Leads total</td>
            <td>Leads bons</td>
            <td>Leads extraits</td>
            <td>Leads mauvais</td>
            <td>Leads archivés</td>
        </tr>
        @foreach ($landings as $landing)
            <tr>
                <td>{{ $landing['name'] }}<br><small>{{ $landing['source'] }}</small></td>
                <td>{{ $landing['type'] }}</td>
                <td>{{ $landing['data']['total'] }}</td>
                <td>{{ $landing['data']['new'] }}</td>
                <td>{{ $landing['data']['extracted'] }}</td>
                <td>{{ $landing['data']['trashed'] }}</td>
                <td>{{ $landing['data']['archived'] }}</td>
            </tr>
        @endforeach
    </table>

    </body>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin:50px auto;
        }
        /* Zebra striping */
        tr:nth-of-type(odd) {
            background: #eee;
        }
        th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }
        td, th {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 18px;
        }
    </style>
</html>
