@extends("portal.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
   <div class="container">
          <div class="order-detail-page">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
            




                <!-- <div class="row mt-3">
                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_length" id="column-filter_length">
                        </div>
                    </div>


                </div> -->
                
                <div class="order-totel-details">
                    <div class="card">
                    <div class="card-body">
                    <div class="col-sm-12">
                                        <h4>Order NO: 20221017113 </h4>
                <div class="table-responsive">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>: 20221017113</th>
                            <th>Customer</th>
                            <th>: HARIS</th>
                        </tr>
                        <tr>
                            <th>Bill No.</th>
                            <th>: 14634d88e89d7ee1666025704</th>
                            <th>Delivery Address</th>
                            <th>:                                  The Dubai Mall, <br>
                                Dubai,<br>
                                Afghanistan,<br>
                                ,<br>
                                44 45645645<br>

                           
                                                      </th>
                        </tr>
                        <tr>
                            <th>Created on.</th>
                            <th>: 17-Oct-22 17:55 PM</th>
                            <th>Sale Amount</th>
                            <th>: 300.00</th>
                        </tr>
                      

                        <tr>
                            <th>Sub Total </th>
                            <th>: 300.00</th>
                            <th>Discount</th>
                            <th>: 0.00
                                                            </th>
                        </tr>
                         
                        <tr>
                            <th>Grand Total</th>
                            <th>: 300.00 </th>
                            <th>Mode of Delivery</th>
                            <th>:         WALLET
                                
                            </th>
                        </tr>
                    </thead>

                </table>
                </div>
                    </div>
                    </div>
                    </div>
                </div>
                <div class="order-totel-details">
                <div class="card">
                <div class="card-body">
                <!-- <div class="col-sm-12" style="padding-top: 20px;">
                                <h4>Order Items <a href="https://jarsite.com/moda/public/admin/order_edit/1">edit</a></h4>
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Vendor</th>
                            <th>Item</th>
                            <th>Quantity</th>
                           
                           <th> Shipping Charge </th>
                            <th>Total</th>
                            <th>Change Status</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                                               <tr>
                            <td>
                                                                <img src="https://jarsite.com/moda/public/uploads/products" style="width:100px;height:100px;object-fit:cover;">
                                </td>
                            <td>Alex</td>
                            <td>Sport Wear</td>
                            <td>2</td>
                            
                            <td>0.00</td>
                            <td>300moda</td> 
                            <td></td>
                            <td>Order Placed</td>
                           </tr>
                    
                                        </tbody>
                </table>
                         
               </div> -->
                <div class="order-page-infomatics">
                                        <form>
                        <div class="action-divs d-flex align-items-center">
                            <div class="checkbox-dsign-order-select">
                                <input type="checkbox">
                                <label>Select All</label>
                            </div>
                            <div class="cancel_btn">
                                <button class="cancel-selection">Cancel</button>
                            </div>
                            
                        </div>
                        <div class="product-order-details-div">
                                                        <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Products</h4>
                                    </div>
                                    <div class="product_details-flex d-flex">
                                        <div class="producT_img">
                                                                                        <img src="https://jarsite.com/moda/public/uploads/products" style="width:100px;height:100px;object-fit:cover;">
                                                                                    </div>
                                        <div class="product_content">
                                            <h4 class="product-name">Sport Wear</h4>
                                            <p><strong>Vendor: </strong> Alex</p>
                                            <p><strong>Quantity: </strong> 2</p>
                                            <!-- <p><strong>Shipping Charge: </strong> 0.00</p> -->
                                            <p><strong>Total: </strong> 300</p>
                                            <div class="action-divs d-flex align-items-center">
                                                                                                <div class="checkbox-dsign-order-select">
                                                    <input type="checkbox">
                                                    <label>Select</label>
                                                </div>
                                                <div class="cancel_btn">
                                                    <button class="cancel-selection" data-role="status-change" href="https://jarsite.com/moda/public/admin/order/change_status" detailsid="147" value="10">Cancel</button>

                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Delivery Status</h4>
                                    </div>
                                    <div class="delivery-status-block">
                                        <ul class="list-unstyled">
                                            <li class="pending  active "><button class="btn-design">Pending</button></li>
                                                                                        <li class="accepted  active "><button class="btn-design">Accepted</button></li>
                                            <li class="ready-for-delivery "><button class="btn-design">Ready For Delivery</button></li>
                                            <li class="dispatched "><button class="btn-design">Dispatched</button></li>
                                            <li class="delivered "><button class="btn-design">Delivered</button></li>
                                            
                                        </ul>
                                   </div>

                                   <select class="form-control" data-role="status-change" href="https://jarsite.com/moda/public/admin/order/change_status" detailsid="147" style="display: none;">
                                <option value="0">Pending</option>
                                <option value="1" selected="">Accepted</option>
                                <option value="2">Ready for Delivery</option>
                                <option value="3">Dispatched</option>
                                <option value="4">Delivered</option>
                                <option value="10">Cancelled</option>
                            </select>
                                </div>
                            </div>
                                                    </div>
                    </form>
                                    </div>
            </div>
            </div>
             </div>
            </div>
    </div>
      </div>
@stop
