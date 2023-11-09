
@php
set_time_limit(25000000000);
@endphp

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
<link href="http://fonts.cdnfonts.com/css/verdana" rel="stylesheet">


<head>
    <title>Atlas Order Details</title>
</head>

<style>


*{
    font-family: 'Verdana', sans-serif;
}

    .item-img {
        width: 40px;
        height: 40px;
    }

    th {
        background-color: black;
        color: white;
        padding-left: 10px;
    }

    table,
    td,
    th {
        border: 1px solid cadetblue;
    }

    td {
        font-size: 10px;
        padding-left: 8px;

    }

    .table-value-custom {
        font-size: 13px;
    }

    .center-text{
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .thead-custom {
        font-size: 15px;
        font-weight: bold;
        color: #ffffff;
        text-align: center;
    }



    .vendor-logo {
        width: 30px;
        height: 15px;
    }


    .com-logo {
        float: right;
        top: 150px;
        width: 30%;
        height: 70px;
    }

    .right-align{
        text-align: right;
        padding-right: 10px;
    }

    .top-title {
        font-size: 20px;
    }

    .sub-top-title {
        font-size: 15px;
    }

    .dealer-name {
        font-size: 15px;
    }



    .table-custom {
        margin-bottom: 20px;
    }

    .each-total-text {
        font-weight: 900;
        color: darkgreen;
        margin-bottom: 0px;
        font-size: 15px;
        padding-left: 5px;
        padding-right: 10px;
        text-align: right;
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .each-total-cate-text {
        text-align: right;
        font-weight: 900;
        color: darkgreen;
        margin-bottom: 0px;
        font-size: 15px;
        padding-right: 5px;
        padding-top: 5px;
        padding-bottom: 5px;


    }

    .top-title-table {
        font-weight: bold;
        color: #ffffff;
        background-color: #115085;
        margin-bottom: 0px;
        padding-left: 10px;
        font-size: 13px;
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .table>* {
        padding-bottom: 0px !important;
        padding-top: 0px !important;
    }

    .table-wrapper{
        margin-top: 80px
    }

</style>

<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-6">

                <h2 class="top-title">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'ATLAS 2023 ATLAS VIRTUAL SHOW') }}   <br> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'SUMMARY') }}  <br> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'SUMMARY SALES DISPLAY') }}</h2>
                @if($vendor != null && $vendor->vendor_name)
                <h2 class="dealer-name">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Vendor Name:') }}   {{ $vendor->vendor_name }}</h2>
                @else
                <h2 class="dealer-name">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Vendor Name: No name found') }}  </h2>
                @endif

                @if($vendor != null && $vendor->vendor_code)
                <h2 class="dealer-name">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Vendor Account') }}   #: {{ $vendor->vendor_code }}</h2>
                @else
                <h2 class="dealer-name">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Vendor Account #: No vendor code found') }}  </h2>
                @endif

                <h2 class="dealer-name">Date: {{ $printed_at }} (MST)</h2>

            </div>
            <div>
                <img src="https://atlasbookingprogram.com/assets/atlas-lgo.png" class="com-logo" alt="">
            </div>
        </div>
    </div>






    <div class="table-wrapper">




                <div class="table-responsive">
                    <table class="">
                        <thead>
                            <tr>

                                <th class="thead-custom"> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'Qty') }} </th>
                                <th class="thead-custom">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Atlas') }}  #</th>
                                <th class="thead-custom"> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'Vendor') }} #</th>
                                <th class="thead-custom"> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'Description') }}</th>
                                <th class="thead-custom"> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'UM') }}</th>

                                <th class="thead-custom">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Regular ') }}  ($)</th>
                                <th class="thead-custom">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Show ') }}  ($)</th>

                                <th class="thead-custom">{{ App\Http\Controllers\DealerController::staticTrans($lang, 'Total') }}  ($)</th>
                            </tr>
                        </thead>

                        @if (count($data) > 0)

                        <tbody>

                            @foreach ($data as $item)
                                <tr>
                                    <td class="table-value-custom center-text">
                                        {{ $item->qty }}
                                    </td>
                                    <td class="table-value-custom center-text">
                                        {{ $item->atlas_id }}
                                    </td>

                                    <td class="table-value-custom center-text">
                                        {{ $item->vendor }}
                                    </td>

                                    <td class="table-value-custom center-text">
                                        {{ $item->description }}
                                    </td>
                                    <td class="table-value-custom center-text">
                                        {{ $item->um }}
                                    </td>
                                    <td class="table-value-custom right-align">
                                        {{ number_format(floatval($item->regular), 2) }}
                                    </td>
                                    <td class="table-value-custom right-align">
                                        {{ number_format($item->booking, 2) }}
                                    </td>
                                    <td class="table-value-custom right-align">
                                        {{ number_format($item->total, 2) }}
                                    </td>
                                </tr>

                            @endforeach

                            <tr>
                                <td colspan="6">
                                    <h5 class="each-total-cate-text" style="">

                                        {{ App\Http\Controllers\DealerController::staticTrans($lang, 'TOTAL') }} ($)
                                        </h5>
                                </td>
                                <td>
                                    <h5 class="each-total-text" style="">
                                        {{ number_format( $grand_total, 2) }}</h5>
                                </td>
                            </tr>

                        </tbody>
                        @endif
                    </table>
                </div>

    </div>




{{--

    @if (count($data) > 0)
    <div style="width: 100%; text-align: right; border: 1px solid gray; margin-top: 20px">

        <h5 class="each-total-cate-text" style="display: inline-block; border-right: 1px solid gray"> {{ App\Http\Controllers\DealerController::staticTrans($lang, 'Grand Total') }}   ($):
        </h5>
        <h5 class="each-total-text" style="display: inline-block; padding-right: 30px">
            {{ number_format($grand_total, 2) }}
        </h5>
    </div>
@endif --}}




</body>

</html>
