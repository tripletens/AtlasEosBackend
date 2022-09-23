<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

///////////////// Admin /////////////
Route::group(
    [
        'namespace' => 'App\Http\Controllers',
        ///  'middleware' => 'cors',
    ],
    function () {
        Route::post('/admin-login', 'AdminController@admin_login');

        Route::get(
            '/admin/get-sales-rep-users/{user}',
            'AdminController@get_sales_rep_users'
        );

        Route::get(
            '/admin/get-branch-manager-users/{user}',
            'AdminController@get_branch_manager_users'
        );

        Route::post(
            '/admin/atlas-product-upload-format',
            'AdminController@atlas_format_upload_new_product_csv'
        );

        Route::get(
            '/admin/get-vendor-items',
            'AdminController@get_vendors_with_items'
        );

        Route::get('/admin/get-aims-export', 'AdminController@aims_exports');

        Route::get(
            '/admin/get-dealer-detailed-report',
            'AdminController@dealer_detailed_report'
        );

        Route::get(
            '/admin/analysis-admin-dashboard',
            'AdminController@admin_dashboard_analysis'
        );

        Route::get(
            '/admin/get-unread-report',
            'AdminController@get_unread_report'
        );

        Route::get(
            '/admin/most-sales-dealers-admin-dashboard',
            'AdminController@most_sales_dealer_admin_dashboard'
        );

        Route::get(
            '/admin/most-sales-vendor-admin-dashboard',
            'AdminController@most_sales_vendors_admin_dashboard'
        );

        Route::get(
            '/get-all-vendor-users',
            'AdminController@get_all_vendor_users'
        );

        Route::post('/upload-users', 'AdminController@upload_users');
        Route::post('/upload-vendors', 'AdminController@upload_vendors');

        Route::post(
            '/atlas-format-upload-vendors',
            'AdminController@atlas_format_upload_vendors'
        );

        Route::post('/register-vendors', 'AdminController@register_vendors');

        Route::post(
            '/register-dealership',
            'AdminController@register_dealership'
        );

        Route::post(
            '/register-vendor-users',
            'AdminController@register_vendor_users'
        );

        Route::post(
            '/register-dealer-users',
            'AdminController@register_dealer_users'
        );

        Route::get(
            '/get-all-vendor-users',
            'AdminController@get_all_vendor_users'
        );

        Route::post(
            '/upload-vendor-users',
            'AdminController@upload_vendor_users'
        ); # working fine
        Route::post(
            '/atlas-format-upload-vendor-users',
            'AdminController@atlas_format_upload_vendor_users'
        ); # working fine
        Route::post('/upload-admin', 'AdminController@upload_admin'); # working fine
        Route::post('/edit-vendor-data', 'AdminController@edit_vendor_data');
        Route::get(
            '/deactivate-vendor/{id}',
            'AdminController@deactivate_vendor'
        );

        Route::get('/activate-vendor/{id}', 'AdminController@activate_vendor');

        Route::get(
            '/admin/activate-vendor-switch',
            'AdminController@activate_vendor_switch'
        );

        Route::get(
            '/admin/deactivate-vendor-switch',
            'AdminController@deactivate_vendor_switch'
        );

        Route::post(
            '/admin/edit-dealer-data',
            'AdminController@edit_dealer_data'
        );

        Route::get(
            '/deactivate-vendor-user/{id}',
            'AdminController@deactivate_vendor_user'
        );
        Route::get(
            '/activate-vendor-user/{id}',
            'AdminController@activate_vendor_user'
        );

        Route::get('/get-vendor-user/{id}', 'AdminController@get_vendor_user');

        Route::post('/upload-dealers', 'AdminController@upload_dealers');

        Route::post(
            '/edit-vendor-user',
            'AdminController@edit_vendor_user_data'
        );

        Route::post(
            '/upload-dealer-users',
            'AdminController@upload_dealer_users'
        );

        Route::post(
            '/atlas-format-upload-dealer-users',
            'AdminController@atlas_format_upload_dealer_users'
        );

        Route::get(
            '/get-all-dealer-users',
            'AdminController@get_all_dealer_users'
        );

        Route::get(
            '/deactivate-dealer-user/{id}',
            'AdminController@deactivate_dealer_user'
        );

        Route::post(
            '/upload-product-csv',
            'AdminController@upload_product_csv'
        );

        Route::post(
            '/upload-new-product-csv',
            'AdminController@upload_new_product_csv'
        );

        Route::get('/all-products', 'AdminController@get_all_products');
        Route::get(
            '/deactivate-product/{id}',
            'AdminController@deactivate_product'
        );

        Route::get(
            '/get-edit-product/{id}',
            'AdminController@get_edit_product'
        );

        Route::get('/get-product/{id}', 'AdminController@get_product');
        Route::get(
            '/get-product-atlas-id/{id}',
            'AdminController@get_product_by_atlas_id'
        );

        Route::get(
            '/admin/get-vendor-by-code/{code}',
            'AdminController@get_vendor_by_code'
        );

        Route::get(
            '/admin/get-dealership-by-code/{code}',
            'AdminController@get_dealership_by_code'
        );

        Route::get(
            '/admin/get-item-by-atlas/{code}',
            'AdminController@get_item_by_atlas'
        );

        Route::post('/edit-product', 'AdminController@edit_product');
        Route::post('/add-product', 'AdminController@add_product');

        // Route::get(
        //     '/deactivate-product/{id}',
        //     'AdminController@deactivate_product'
        // );

        Route::post('/upload-admin-users', 'AdminController@upload_admin_csv');

        Route::post(
            '/atlas-format-upload-admin-users',
            'AdminController@atlas_format_upload_admin_csv'
        );

        Route::post(
            '/register-admin-users',
            'AdminController@register_admin_users'
        );

        Route::get('/all-admins', 'AdminController@get_all_admins');
        Route::get(
            '/deactivate-admin/{id}',
            'AdminController@deactivate_admin'
        );

        Route::post('/create-faq', 'AdminController@create_faq');
        Route::post('/edit-faq', 'AdminController@edit_faq');

        Route::post('/create-seminar', 'AdminController@create_seminar');

        Route::get('/get-all-seminar', 'AdminController@get_all_seminar');

        Route::get('/deactivate-faq/{id}', 'AdminController@deactivate_faq');

        Route::get('/get-faq-id/{id}', 'AdminController@get_faq_id');

        Route::post('/save-chat-id', 'AdminController@save_chat_id');

        Route::get('/get-all-users', 'AdminController@get_all_users');

        Route::get(
            '/get-all-admin-users/{user}',
            'AdminController@get_all_admin_users'
        );

        Route::get(
            '/admin/get-vendor-unread-msg/{user}',
            'AdminController@get_vendor_unread_msg'
        );

        Route::get(
            '/admin/get-dealer-unread-msg/{user}',
            'AdminController@get_dealer_unread_msg'
        );

        Route::get(
            '/admin/get-users-unread-msg/{user}',
            'AdminController@get_users_unread_msg'
        );

        Route::get(
            '/admin/get-all-reports',
            'AdminController@admin_get_all_reports'
        );

        Route::get(
            '/get-all-reports/{user_id}',
            'AdminController@fetch_reports_by_user_id'
        );

        // get_all_reports
        Route::post('/save-countdown', 'AdminController@save_countdown');

        Route::get('/get-countdown', 'AdminController@get_countdown');

        Route::get(
            '/admin/get-report-problem/{ticket}',
            'AdminController@get_report_problem'
        );

        Route::post(
            '/admin/reply-report',
            'AdminController@admin_reply_report'
        );

        Route::get(
            '/admin/all-promotional-flyer',
            'AdminController@get_all_promotional_flyer'
        );

        Route::get(
            '/admin/get-promotional-flyer/{id}',
            'AdminController@get_current_promotional_flier'
        );

        Route::get(
            '/admin/get-seminar-id/{id}',
            'AdminController@get_seminar_by_id'
        );

        Route::post('/edit-seminar', 'AdminController@edit_seminar');

        Route::post(
            '/admin/save-admin-report-reply',
            'AdminController@save_admin_reply_problem'
        );

        Route::get(
            '/admin/get-current-ticket/{ticket}',
            'AdminController@get_first_ticket'
        );

        Route::get(
            '/admin/get-report-reply/{ticket}',
            'AdminController@get_report_reply'
        );

        Route::get('/admin/vendor-notes', 'AdminController@get_vendor_notes');

        Route::get('/admin/atlas-notes', 'AdminController@get_atlas_notes');

        Route::post(
            '/admin/save-price-override',
            'AdminController@save_price_override'
        );

        Route::get(
            '/admin/get-price-override/{dealer}/{atlas_id}',
            'AdminController@get_price_override_item'
        );

        Route::get(
            '/admin/get-price-override-report',
            'AdminController@get_price_overide_report'
        );

        Route::get(
            '/admin/get-special-orders',
            'AdminController@get_special_orders'
        );

        Route::get('/admin/get-all-vendors', 'AdminController@get_all_vendor');

        Route::get(
            '/admin/get-vendor-products/{code}',
            'AdminController@get_vendor_products'
        );

        Route::get('/admin/dealer-summary', 'AdminController@dealer_summary');

        Route::get(
            '/admin/view-dealer-summary/{code}',
            'AdminController@view_dealer_summary'
        );

        Route::get(
            '/admin/dealer-single-summary/{code}',
            'AdminController@dealer_single_summary'
        );

        Route::get(
            '/admin/get-active-countdown',
            'AdminController@get_active_countdown'
        );

        Route::get(
            '/admin/vendor-summary/{code}',
            'AdminController@vendor_summary'
        );

        Route::get(
            '/admin/deactivate-dealers',
            'AdminController@deactivate_all_dealers'
        );

        Route::get(
            '/admin/activate-dealers',
            'AdminController@activate_all_dealers'
        );

        Route::get(
            '/admin/deactivate-vendors',
            'AdminController@deactivate_all_vendors'
        );

        Route::get(
            '/admin/get-all-users-status',
            'AdminController@get_users_status'
        );

        Route::get(
            '/admin/activate-vendors',
            'AdminController@activate_all_vendors'
        );

        Route::get(
            '/admin/get-all-company',
            'AdminController@get_user_company'
        );

        Route::get(
            '/admin/get-chat-selected-vendor-users/{code}',
            'AdminController@get_chat_selected_vendor_users'
        );

        Route::get(
            '/admin/all-dealership',
            'AdminController@get_all_dealership'
        );

        Route::get('/testing', 'AdminController@testing_api');
    }
);

///////////////// Users (DEALERS AND VENDORS) /////////////
Route::group(
    [
        'namespace' => 'App\Http\Controllers',
        /////'middleware' => 'cors'
    ],
    function () {
        Route::post('/login', 'UserController@login');
        Route::get('/get-all-vendors', 'VendorController@get_all_vendors');
        Route::post('/create-report', 'DealerController@create_report');
        Route::get('/fetch-all-faqs', 'DealerController@fetch_all_faqs');
        Route::get('/dealer-faqs', 'DealerController@dealer_faq');

        Route::get(
            '/dealer/get-branch-manager-users/{user}',
            'DealerController@get_branch_manager_users'
        );

        Route::get(
            '/dealer/get-sales-rep-users/{user}',
            'DealerController@get_sales_rep_users'
        );

        Route::get(
            '/dealer/dealer-privileged-dealers/{user}',
            'DealerController@get_dealers_privileged_dealers'
        );

        Route::post(
            '/dealer/check-program-state',
            'DealerController@check_end_program'
        );

        Route::get(
            '/dealer/unread-report-reply/{user}',
            'DealerController@get_unread_report_reply'
        );

        Route::get(
            '/dealer/update-report-ticket/{ticket}',
            'DealerController@update_report_ticket'
        );

        Route::get(
            '/dealer/get-vendor-data/{code}',
            'VendorController@get_vendor_data'
        );

        Route::get(
            '/dealer/get-vendor-item/{vendor}/{atlas}',
            'DealerController@get_vendor_item'
        );

        Route::post(
            '/dealer/save-edited-user-order',
            'DealerController@save_edited_user_order'
        );

        Route::post(
            '/dealer/remove-dealer-order-item',
            'DealerController@remove_dealer_order_item'
        );

        Route::get(
            '/dealer/get-dealer-vendor-orders/{dealer}/{vendor}',
            'DealerController@get_user_vendor_order'
        );

        Route::post(
            '/dealer/move-dealer-quick-order',
            'DealerController@move_dealer_quick_order_to_cart'
        );

        Route::post(
            '/dealer/submit-quick-order',
            'DealerController@submit_quick_order'
        );

        Route::post(
            '/dealer/save-quick-order-changes',
            'DealerController@save_quick_order_changes'
        );

        Route::get(
            '/dealer/get-dealer-quick-orders/{dealer}/{uid}',
            'DealerController@get_dealer_quick_orders'
        );

        Route::get(
            '/dealer/delete-quick-order-item/{user}/{atlas_id}',
            'DealerController@delete_quick_order_item'
        );

        Route::get(
            '/dealer/remove-all-user-order/{user}',
            'DealerController@remove_all_quick_order'
        );

        Route::get(
            '/dealer/get-item-group/{group}',
            'DealerController@get_item_grouping'
        );

        Route::post(
            '/dealer/submit-assorted-quick-order',
            'DealerController@submit_assorted_quick_order'
        );

        Route::post(
            '/dealer/save-dealer-reply',
            'DealerController@save_dealer_reply_problem'
        );

        Route::post(
            '/vendor/save-vendor-notes',
            'VendorController@save_vendor_notes'
        );

        Route::get(
            '/vendor/get-privileged-dealers/{code}',
            'VendorController@get_privileged_dealers'
        );

        Route::post(
            '/vendor/save-atlas-notes',
            'VendorController@save_atlas_notes'
        );

        Route::get(
            '/dealer/get-problem-ticket/{ticket}',
            'DealerController@get_problem_dealer'
        );

        Route::get(
            '/dealer/get-report-replies/{ticket}/{dealer}',
            'DealerController@get_report_reply'
        );

        Route::get(
            '/dealer/get-ticket-first/{ticket}/{dealer_id}',
            'DealerController@get_first_ticket'
        );

        Route::get(
            '/get-vendor-products/{code}',
            'DealerController@get_vendor_products'
        );

        Route::get(
            '/dealer/get-vendor-products/{code}',
            'DealerController@dealer_get_vendor_products'
        );

        Route::get(
            '/dealer/get-orders-by-vendor/{code}',
            'DealerController@get_editable_orders_by_vendor'
        );

        Route::get(
            '/dealer/get-ordered-vendor/{code}',
            'DealerController@get_ordered_vendor'
        );

        Route::get(
            '/dealer/delete-item-cart/{dealer}/{vendor}',
            'DealerController@delete_item_cart'
        );

        // delete cart item by atlas_id and dealer_id
        Route::get(
            '/dealer/delete-item-cart-dealer-id-atlas-id/{dealer_id}/{atlas_id}',
            'DealerController@delete_item_cart_atlas_id_dealer_id'
        );

        Route::post(
            '/dealer/edit-cart-order',
            'DealerController@edit_dealer_order'
        );

        Route::get(
            '/quick-order-filter-atlasid/{id}',
            'DealerController@quick_order_filter_atlasid'
        );

        Route::post('/add-item-to-cart', 'DealerController@add_item_cart');

        Route::post(
            '/dealer/save-item-to-cart',
            'DealerController@save_item_cart'
        );

        Route::get(
            '/dealer/get-item-by-atlas-vendor-code/{code}',
            'DealerController@get_fetch_by_vendor_atlas'
        );

        Route::get(
            '/universal-search/{search}',
            'DealerController@universal_search'
        );

        Route::get(
            '/dealer-dashboard/{account}',
            'DealerController@dealer_dashboard'
        );

        // fetch all remaining orders - vendors that dealer didnt order from
        Route::get(
            '/fetch-orders-remaining/{account}',
            'DealerController@fetch_orders_remaining'
        );

        //---------------------- seminar apis here -------------------- //
        // Route::post('/create-seminar', 'SeminarController@create_seminar');
        Route::get(
            '/fetch-all-seminars/{dealer_id}',
            'SeminarController@fetch_all_seminars'
        );

        Route::get(
            '/fetch-scheduled-seminars/{dealer_id}',
            'SeminarController@fetch_scheduled_seminars'
        );

        Route::get(
            '/fetch-ongoing-seminars/{dealer_id}',
            'SeminarController@fetch_ongoing_seminars'
        );

        Route::get(
            '/fetch-watched-seminars/{dealer_id}',
            'SeminarController@fetch_watched_seminars'
        );

        Route::post('/join-seminar', 'SeminarController@join_seminar');

        Route::get(
            '/fetch-all-dealers-in-seminar/{seminar_id}',
            'SeminarController@fetch_all_dealers_in_seminar'
        );

        Route::get(
            '/fetch_only_dealer_emails/{seminar_id}',
            'SeminarController@fetch_only_dealer_emails'
        );

        Route::post('/edit-seminar', 'SeminarController@edit_seminar');

        // ------------------promotional flier ------------------//

        Route::post(
            '/create-promotional-flier',
            'PromotionalFlierController@create_promotional_flier'
        );

        Route::get(
            '/show-all-promotional-fliers',
            'PromotionalFlierController@show_all_promotional_fliers'
        );

        Route::get(
            '/show-promotional-flier-by-id/{id}',
            'PromotionalFlierController@show_promotional_flier_by_id'
        );
        Route::get(
            '/show-promotional-flier-by-vendor-id/{vendor_id}',
            'PromotionalFlierController@show_promotional_flier_by_vendor_id'
        );

        // hshkdksjdsd
        // sdjsjfkjds
        Route::post(
            '/edit-promotional-flier/{id}',
            'PromotionalFlierController@edit_promotional_flier'
        );

        Route::get(
            '/delete-promotional-flier/{id}',
            'PromotionalFlierController@delete_promotional_flier'
        );

        Route::get(
            '/switch-promotional-flier-status/{id}',
            'PromotionalFlierController@switch_promotional_flier_status'
        );

        // ------------------ new products --------------------//
        Route::get(
            '/products/new',
            'ProductsController@fetch_all_new_products'
        );
        Route::get(
            '/products/new/vendor_id/{vendor_id}',
            'ProductsController@sort_newproduct_by_vendor_id'
        );
        Route::get(
            '/products/new/atlas_id/{atlas_id}',
            'ProductsController@sort_newproduct_by_atlas_id'
        );

        Route::get(
            '/get-product-by-vendor-id/{vendor_id}',
            'ProductsController@fetch_all_products_by_vendor_id'
        );

        Route::get(
            '/get-all-new-products',
            'ProductsController@fetch_all_new_products'
        );
        Route::get(
            '/fetch-all-products-by-vendor-code/{vendor_code}',
            'ProductsController@fetch_all_products_by_vendor_code'
        );

        //----------------------------- new products ends here ------------------//

        Route::get(
            '/vendor/get-vendor-coworkers/{code}/{user}',
            'VendorController@get_vendor_coworkers'
        );

        Route::get(
            '/dealer/get-dealer-coworkers/{code}/{user}',
            'DealerController@get_dealer_coworkers'
        );
        Route::get(
            '/sort-newproduct-by-vendor-id/{vendor_id}',
            'ProductsController@sort_newproduct_by_vendor_id'
        );
        Route::get(
            '/sort-newproduct-by-atlas-id/{atlas_id}',
            'ProductsController@sort_newproduct_by_atlas_id'
        );

        Route::get(
            '/vendor/get-dealers',
            'VendorController@get_distinct_dealers'
        );

        Route::get(
            '/vendor/get-selected-company-dealers/{code}/{user}',
            'VendorController@get_company_dealer_users'
        );

        Route::get(
            '/vendor/get-vendor-unread-msg/{user}',
            'VendorController@get_vendor_unread_msg'
        );

        Route::get('/dealer/get-vendors', 'DealerController@get_vendor');
        Route::get(
            '/dealer/get-vendors-with-orders',
            'DealerController@get_vendors_with_orders'
        );

        Route::get(
            '/dealer/get-selected-company-vendor/{code}/{user}',
            'DealerController@get_company_vendor_users'
        );

        Route::get(
            '/dealer/get-dealer-unread-msg/{user}',
            'DealerController@get_dealer_unread_msg'
        );

        Route::get(
            '/vendor/get-privileged-vendors/{user}/{code}',
            'VendorController@get_privileged_vendors'
        );

        Route::get(
            '/vendor/get-vendor-order-data/{code}',
            'VendorController@get_vendor_orders'
        );

        Route::get(
            '/vendor/vendor-dashboard-analysis/{user}',
            'VendorController@vendor_dashboard_analysis'
        );

        Route::get(
            '/vendor/vendor-dashboard-most-purchaser/{user}',
            'VendorController@vendor_dashboard_most_purchaser'
        );

        Route::get(
            '/vendor/vendor-single-dashboard-most-purchaser/{code}',
            'VendorController@vendor_single_dashboard_most_purchaser'
        );

        Route::get(
            '/vendor/vendor-dashboard-sup-most-purchaser/{user}',
            'VendorController@vendor_dashboard_sup_most_purchaser'
        );

        Route::get(
            '/vendor/vendor-single-dashboard-analysis/{code}',
            'VendorController@vendor_single_dashboard_analysis'
        );

        Route::get(
            '/vendor/get-vendor-products/{code}',
            'VendorController@get_vendors_products'
        );

        Route::get(
            '/cart/dealer/{dealer_id}',
            'DealerController@fetch_all_cart_items'
        );

        // adds item to the quick order
        Route::post('/quick_order', 'DealerController@add_quick_order');

        Route::get(
            '/vendor/get-sales-by-item-summary/{code}',
            'VendorController@sales_by_item_summary'
        );

        Route::get(
            '/vendor/change-bell-notify-status/{user}/{vendor}',
            'VendorController@change_user_bell_status'
        );

        ////// Export /////

        Route::get(
            '/vendor/get-sales-by-item-detailed-export/{code}',
            'VendorController@sales_by_item_detailed_export'
        );

        Route::get(
            '/vendor/get-sales-by-item-detailed/{code}',
            'VendorController@sales_by_item_detailed'
        );

        Route::get(
            '/vendor/get-vendor-order-bell-count/{code}',
            'VendorController@get_vendor_rece_orders'
        );

        Route::get(
            '/vendor/get-purchases-dealers/{code}',
            'VendorController@get_purchases_dealers'
        );

        Route::get(
            '/vendor/get-vendor-notes/{dealer}/{vendor}',
            'VendorController@get_vendor_note'
        );

        Route::get(
            '/vendor/get-atlas-notes/{dealer}/{vendor}',
            'VendorController@get_atlas_note'
        );

        Route::get(
            '/vendor/view-dealer-summary/{dealer}/{vendor}',
            'VendorController@view_dealer_summary'
        );

        Route::get(
            '/vendor/view-dealer-purchaser-summary/{user}/{dealer}/{vendor}',
            'VendorController@view_dealer_purchaser_summary'
        );

        Route::get('/vendor/get-vendor-faq', 'VendorController@get_vendor_faq');

        Route::get(
            '/vendor/generate-vendor-purchaser-summary/{user}/{dealer}/{vendor}/{lang}/{created_time}',
            'VendorController@generate_vendor_purchasers_summary'
        );

        Route::get(
            '/vendor/get-vendor-special-orders/{user}',
            'VendorController@get_vendor_special_orders'
        );

        Route::get(
            '/vendor/get-special-orders-by-vendor/{code}',
            'VendorController@get_special_orders_by_vendor'
        );

        Route::get(
            '/seminars/remind',
            'SeminarController@select_seminars_to_remind'
        );
        Route::get(
            '/promotional_fliers/vendors',
            'PromotionalFlierController@get_all_vendors_with_promotional_fliers'
        );

        // move quick order to cart
        Route::post(
            '/move-quick-order',
            'DealerController@move_quick_order_to_cart'
        );

        // fetch all the quick order items by dealer_id
        Route::get(
            '/fetch-quick-order-items-dealer-id/{dealer_id}',
            'DealerController@fetch_quick_order_items_dealer_id'
        );

        // fetch all the quick order items by user_id
        Route::get(
            '/fetch-quick-order-items-user-id/{user_id}',
            'DealerController@fetch_quick_order_items_user_id'
        );

        // delete all the quick order items by user_id
        Route::get(
            '/delete-quick-order-items-user-id/{user_id}',
            'DealerController@delete_quick_order_items_user_id'
        );

        // delete all the quick order items by atlas_id
        Route::get(
            '/delete-quick-order-items-atlas-id/{user_id}/{atlas_id}',
            'DealerController@delete_quick_order_items_atlas_id'
        );

        // delete all the quick order items by dealer_id
        Route::get(
            '/delete-quick-order-items-dealer-id/{dealer_id}',
            'DealerController@delete_quick_order_items_dealer_id'
        );

        //fetch quick order items by vendor no and atlas_id
        Route::get(
            '/fetch-quick-order-items-atlas-id-vendor-no/{atlas_id}/{vendor_no}',
            'DealerController@fetch_quick_order_items_atlas_id_vendor_no'
        );

        // fetch dealer cart order by atlas_id and vendor_id
        Route::get(
            '/fetch-order-items-atlas-id-vendor-id/{atlas_id}/{vendor_id}',
            'DealerController@fetch_order_items_atlas_id_vendor_id'
        );

        // delete order by atlas id and user_id
        Route::get(
            '/delete-order-items-atlas-id-user-id/{atlas_id}/{user_id}',
            'DealerController@delete_order_items_atlas_id_user_id'
        );

        // fetch all the vendors that have new products
        Route::get(
            '/fetch-vendors-new-products',
            'DealerController@fetch_vendors_new_products'
        );

        // fetch the dealer purchases per day
        Route::get(
            '/fetch-all-orders-per-day/{account_id}',
            'DealerController@fetch_all_orders_per_day'
        );

        // fetch the vendor purchases per day
        //

        Route::get(
            '/fetch-all-vendor-orders-per-day/{id}',
            'VendorController@fetch_all_vendor_orders_per_day'
        );

        // fetch chart start date
        Route::get('/fetch-start-date', 'DealerController@fetch_start_date');

        // add the chart start date
        Route::post('/add-chart-date', 'AdminController@add_chart_date');

        // ---------------- Branch starts here  ------------------------- //
        Route::get(
            '/branch/get-dealer-order-summary/{uid}',
            'BranchController@get_dealer_order_summary'
        );

        Route::get(
            '/branch/get-dealer-order-summary-account-id/{uid}/{account_id}',
            'BranchController@get_dealers_with_account_id_under_branch'
        );

        Route::get(
            '/branch/dashboard/{uid}',
            'BranchController@branch_dashboard'
        );

        Route::get(
            '/branch/get-privileged-dealer/{user}',
            'BranchController@get_privileged_dealers'
        );

        // ---------------- Branch ends here  --------------------------- //

        //------------------- special orders starts here ---------------- //

        // add a special order item
        Route::post(
            '/special-orders/add',
            'SpecialOrderController@add_special_orders'
        );

        // edit special orders
        Route::post(
            '/special-orders/edit',
            'SpecialOrderController@edit_special_orders'
        );

        // delete special order
        Route::get(
            '/special-orders/delete/{dealer_id}/{id}',
            'SpecialOrderController@delete_special_order'
        );

        // fetch special order by uid
        Route::get(
            '/special-orders/{dealer_id}',
            'SpecialOrderController@fetch_special_order_by_dealer_id'
        );

        //------------------- special orders ends here ------------------ //

        // ------------------ Product summary --------------------------- //

        // fetch product summary by dealer_id
        Route::get(
            '/product-summary/{dealer_id}',
            'SummaryController@product_summary'
        );

        // get_dealer_with_her_orders
        Route::get(
            '/dealer-product-summary/{uid}',
            'SummaryController@get_dealers_with_orders'
        );

        // fetch all the orders per branch per dealer
        Route::get(
            '/branch-dealers-orders/{uid}',
            'BranchController@get_dealers_with_account_id_under_branch_with_orders'
        );

        // fetch all branch dealers
        Route::get('/branch-dealers/{uid}', 'BranchController@branch_dealers');
        // ------------------ Product summary ends here ----------------- //

        // ------------------- show bucks starts here  ------------------ //

        Route::post('/add_showbucks', 'BuckController@create_buck');

        Route::get(
            '/fetch_show_buck_promotional_flier/{vendor_code}',
            'BuckController@fetch_show_buck_promotional_flier'
        );

        Route::post('/edit_buck', 'BuckController@edit_buck');

        // ------------------- show bucks ends here  ------------------ //

        // ------------------ test apis --------------------------- //

        // check_seminar_status
        Route::get(
            '/check_seminar_status/{seminar_date}/{start_time}/{stop_time}',
            'SeminarController@check_seminar_status'
        );

        // fetch_show_buck_promotional_flier
        // ------------------ test apis ends here ----------------- //
    }
);

///////////////// Dealer /////////////
// Route::group(
//     ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
//     function () {
//         Route::post('/dealer-login', 'DealerController@login');
//     }
// );

///////////////// Branch /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::post('/branch-login', 'BranchController@login');
    }
);

///////////////// Chat /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::post('/store-chat', 'ChatController@store_chat');

        Route::get(
            '/get-user-chat/{receiver}/{sender}',
            'ChatController@get_user_chat'
        );

        Route::get(
            '/get-user-chat-async/{receiver}/{sender}',
            'ChatController@get_user_chat_async'
        );

        Route::get(
            '/chat/count-unread-msg/{user}',
            'ChatController@count_unread_msg'
        );

        Route::get(
            '/chat/count-unread-msg-role/{user}',
            'ChatController@count_unread_msg_role'
        );

        Route::get(
            '/chat/get-chat-history/{user}/{role}',
            'ChatController@get_chat_history'
        );

        Route::get('/testing-chat', 'ChatController@testing_chat');
    }
);

///////////////// Sales REp /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::get(
            '/sales-rep/dashboard-analysis/{user}',
            'SalesRepController@sales_rep_dashboard_analysis'
        );

        Route::get(
            '/sales-rep/get-purchasers-dealer/{user}',
            'SalesRepController@get_purchases_dealers'
        );

        Route::get(
            '/sales-rep/view-dealer-summary/{user}/{code}',
            'SalesRepController@view_dealer_summary'
        );

        // get the dealers under a salerep
        Route::get(
            '/sales-rep/dealers/{user}',
            'SalesRepController@get_dealers_under_sales_rep'
        );

        // get all the dealer purchases under the salesrep
        Route::get(
            '/sales-rep/dealers-purchases/{user}',
            'SalesRepController@get_sales_rep_dealer_purchases'
        );

        Route::get(
            '/sales-rep/dealers-sales/{user}',
            'SalesRepController@all_dealers_sales'
        );

        Route::get(
            '/sales-rep/dealers-summary/{dealer}',
            'SalesRepController@view_dealer_summary_page'
        );

        Route::get(
            '/sales-rep/dashboard/{user}',
            'SalesRepController@sales_rep_dashboard'
        );
        Route::get(
            '/sales-rep/loggedin-dealers/{user_id}',
            'SalesRepController@fetch_loggedin_dealers'
        );

        Route::get(
            '/sales-rep/notloggedin-dealers/{user_id}',
            'SalesRepController@fetch_notloggedin_dealers'
        );
    }
);
