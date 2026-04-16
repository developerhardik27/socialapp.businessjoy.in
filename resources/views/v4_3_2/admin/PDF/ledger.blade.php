<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        .card {
            border: 1px solid #000;
            border-radius: 10px;
            padding: 12px;
        }

        .title {
            font-size: 18px;
            text-align: left;
        }

        .subtitle {
            font-size: 12px;
            text-align: left;
        }

        .date-range {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .credit {
            color: green;
        }

        .debit {
            color: red;
        }

        .footer-total {
            font-size: 13px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="card">


        <!-- HEADER -->
        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td width="20%" style="vertical-align: middle;">
                    <img @if ($companyDetails['img'] != '') src="{{ public_path('uploads/' . $companyDetails['img']) }}" @endif
                        style="max-width:90px; max-height:90px;" alt="logo">
                </td>

                <td width="80%" style="vertical-align: middle;">
                    <div class="title">Handball Association Gujarat</div>
                    <div class="subtitle">
                        Office Address: {!! nl2br(e(wordwrap($companyDetails['house_no_building_name'], 40, "\n", true))) !!},
                        {!! nl2br(e(wordwrap($companyDetails['road_name_area_colony'], 40, "\n", true))) !!},
                        {{ $companyDetails['city_name'] }}, {{ $companyDetails['state_name'] }} - {{ $companyDetails['pincode'] }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="date-range">
            Ledger Statement From <b>{{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }}</b>
            To <b>{{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</b>
        </div>

        <!-- LEDGER TABLE -->
        <table>
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="30%">Description</th>
                    <th width="15%">Credit</th>
                    <th width="15%">Debit</th>
                    <th width="15%">Balance</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($ledgers as $ledger)
                    <tr>
                        <td class="center">
                            {{ \Carbon\Carbon::parse($ledger['date'])->format('d-M-Y') }}
                        </td>

                        <td class="left">
                            {!! $ledger['description'] ?? '-' !!}
                        </td>

                        <td class="right credit">
                            {{ $ledger['credited'] > 0 ? number_format($ledger['credited'], 2) : '-' }}
                        </td>

                        <td class="right debit">
                            {{ $ledger['debited'] > 0 ? number_format($ledger['debited'], 2) : '-' }}
                        </td>

                        <td class="right">
                            {{ number_format($ledger['balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <!-- FOOTER TOTAL -->
            <tfoot>
                <tr class="footer-total">
                    <td colspan="2" class="right">Total</td>
                    <td class="right credit">{{ number_format($totalCredited, 2) }}</td>
                    <td class="right debit">{{ number_format($totalDebited, 2) }}</td>
                    <td class="right">{{ number_format($totalBalance, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    </div>
</body>
</html>
