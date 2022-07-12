@extends('layouts.mail')

@section('content')

<div class="container-fluid">
    <span class="alert alert-info text-start">
        <h3>Order Deletion Notification</h3> <br/>
        <p>Sorry your order has been deleted successfully. </p> <br/>
        <p><b>Order Details are </b> </p>
        {{ var_dump($data) }}
        {{--  <p><b>Vendor Name: {{ ucwords($data->vendor_name)}} </b> </p>  --}}
        <p><b>Vendor Code: {{ ucwords($data->product_vendor_code)}} </b> </p>
        <p><b>Product Name: {{ ucwords($data->product_description)}} </b> </p>
        <p><b>Product Code: {{ ucwords($data->product_vendor_product_code)}} </b> </p>
        <p><b>Product Xref: {{ ucwords($data->product_xref)}} </b> </p>
        <p><b>Product Quantity: {{ ucwords($data->qty)}} </b> </p>
        <p><b>Product Price: {{ ucwords($data->price)}} </b> </p>
        <p><b>Product Unit Price: {{ ucwords($data->unit_price)}} </b> </p>
    </span>
</div>

@endsection
