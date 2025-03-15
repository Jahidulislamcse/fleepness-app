<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Invoice</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans&display=swap" rel="stylesheet">

    <style type="text/css">
    * {
    font-family: 'DejaVu Sans', Verdana, Arial, sans-serif;
}

      
        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: lightgray
        }

        .font {
            font-size: 15px;
        }

        .authority {
            /*text-align: center;*/
            float: right
        }

        .authority h5 {
            margin-top: -10px;
            color: green;
            /*text-align: center;*/
            margin-left: 35px;
        }

        .thanks p {
            color: green;
            ;
            font-size: 16px;
            font-weight: normal;
            font-family: serif;
            margin-top: 20px;
        }
        
           .responsive-table {
        width: 100%;
        background: #F7F7F7;
        padding: 0 20px;
        border-collapse: collapse;
    }

    .responsive-table td {
        padding: 10px;
        vertical-align: top;
    }

    .header-left, .header-right {
        display: block;
        width: 100%;
    }

    .header-left img {
        max-width: 100%;
        height: auto;
    }

    .font {
        font-size: 14px;
    }

    @media (min-width: 600px) {
        .header-left, .header-right {
            display: table-cell;
            width: 50%;
        }

        .header-right {
            text-align: right;
        }
    }
    </style>

</head>

<body>
<table width="100%" style="background: #F7F7F7; padding: 0 20px;">
    <tr>
        <td valign="top" style="width: 50%;">
            <h2 style="color: green; font-size: 20px;"><strong>{{ $global_setting_data->title }}</strong></h2>
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path($global_setting_data->logo))) }}" alt="{{ $global_setting_data->title }}" width="80"/> 
        </td>
        <td align="right" style="width: 50%;">
           
Email: {{ $global_setting_data->email }} <br>
Mob: {{ $global_setting_data->phone }} <br>
<p style="margin: 0; display: inline;">{{ $global_setting_data->address }}</p>
           
        </td>
    </tr>
</table>


    <table width="100%" style="background:white; padding:2px;"></table>

    <table width="100%" style="background: #F7F7F7; padding:0 5 0 5px;" class="font">
        <tr>
            <td>
                <p class="font" style="margin-left: 20px;">
                    <strong>Name:</strong> {{ $order->customerInfo->name }} <br>
                    <strong>Email:</strong> {{ $order->customerInfo->email }} <br>
                    <strong>Phone:</strong> {{ $order->customerInfo->phone }} <br>

                    <strong>Address:</strong> {{ $order->customerInfo->address }} <br>
                   
                </p>
            </td>
            <td>
                <p class="font">
                <h3><span style="color: green;">Invoice:</span> #{{ $order->invoice_no }}</h3>
                Order Date: {{ $order->order_date->format('d/m/Y') }} <br>
                Payment Type : {{ $order->payment_method }} </span>
                </p>
            </td>
        </tr>
    </table>
    <br />
    <h3>Products</h3>


    <table width="100%">
        <thead style="background-color: green; color:#FFFFFF;">
            <tr class="font">
                <th>Image</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Total </th>
                <th>SubTotal </th>
            </tr>
        </thead>
        <tbody>

            @foreach ($orderItem as $item)
                <tr class="font">
                    <td align="center">
                       
                        @if ($item->productInfo && $item->productInfo->imagesProduct)
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path($item->productInfo->imagesProduct->path))) }}" alt="{{ $item->productInfo->name }}" width="60" height="60"/>
                    
                    @endif
                    </td>
                    <td align="center">
                       
                        <div>
                            <h5> {{ $item->productInfo->name }}</h5>
                            <h5> Size:{{ $item->stockInfo->size }}</h5>
                        </div>

                    </td>

                    <td align="center">{{ $item->qty }}</td>



                    <td align="center">{{ $item->price }} &nbsp;{{ env('currency') }}</td>

                    <td align="center">{{ $item->price * $item->qty }} &nbsp;{{ env('currency') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <table width="100%" style=" padding:0 10px 0 10px;">
        <tr>
            <td align="right">
                <h2><span style="color: green;">Subtotal:</span>{{ $order->amount }} &nbsp;{{ env('currency') }}</h2>
                <h2><span style="color: green;">Shipping Charge:</span>{{ $order->shipping_cost }} &nbsp;{{ env('currency') }}</h2>
                <h2><span style="color: green;">Total:</span> {{ $order->amount + $order->shipping_cost }} &nbsp;{{$currency }}</h2>
                {{-- <h2><span style="color: green;">Full Payment PAID</h2> --}}
            </td>
        </tr>
    </table>
    <div class="thanks mt-3">
        <p>Thanks For Buying Products..!!</p>
    </div>
    <div class="authority float-right mt-5">
        <p>-----------------------------------</p>
        <h5>Authority Signature:</h5>
    </div>
</body>

</html>
