<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemOrderController;
use App\Http\Controllers\PartyCakesController;
use App\Http\Controllers\PickListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpecialOrdersController;
use App\Http\Controllers\DispatchInventory1Controller;
use App\Http\Controllers\OpicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemsAPIController;
use App\Http\Controllers\APIsController;
use App\Http\Controllers\CashFundController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\MixAndMatchController;
use App\Http\Controllers\ReceivedItemsController;
use App\Http\Controllers\StockCountingLineController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockTransferLineController;
use App\Http\Controllers\LoyaltyCardJsonController;
use App\Http\Controllers\StoreExpenseController;
use App\Http\Controllers\AttendanceRecordController;

use App\Http\Controllers\ApisStockCountingController;
use App\Http\Controllers\ApisStockCountingLineController;
use App\Http\Controllers\AttendanceApiController;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/* Route::post('/login', [AuthController::class, 'login']) */;

Route::post('login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Generate token
    $token = $user->createToken('RAYAPP')->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:sanctum')->post('logout', function (Request $request) {
    $request->user()->tokens->each(function ($token) {
        $token->delete();
    });

    return response()->json(['message' => 'Logged out successfully']);
});


Route::post('/update-counted-value', [ItemOrderController::class, 'updateCountedValue']);
Route::post('/update-all-counted-values', [ItemOrderController::class, 'updateAllCountedValues']);
Route::post('/update-all-counted-values3', [ItemOrderController::class, 'updateAllCountedValues3']);
Route::post('/dispatch/update-all-counted-values', [DispatchInventory1Controller::class, 'updateAllCountedValues']);
Route::post('/sp-update-all-counted-values', [SpecialOrdersController::class, 'updateAllCountedValues']);

/*<==================PICKLIST====================>*/
Route::resource('picklist', PickListController::class);
/* Route::post('/update-adjustment', [PickListController::class, 'updateAdjustment'])->name('update.adjustment'); */
Route::post('/update-actual', [PickListController::class, 'updateActual']);

/*<==================FGCOUNT====================>*/
Route::post('/update-mgcount', [ReportController::class, 'updateMGCount']);
Route::post('/update-counted', [ReportController::class, 'updateCounted']);
Route::post('/save-all-data', [ReportController::class, 'saveAllData'])->name('save.all.data');

Route::get('/api/stores', [PicklistController::class, 'getStores']);
Route::get('/api/picklist/{storeName}', [PicklistController::class, 'getStoreData']);

/*<==================CRATES====================>*/
Route::post('/update-crates-counts', [OpicController::class, 'updateCratesCounts']);
Route::post('/api/get-inventory-data', [OpicController::class, 'getInventoryData']);

/*<==================Partycakes====================>*/
Route::apiResource('partys-cakes', PartycakesController::class);
Route::get('/cakes/{cakeId}/download', [PartyCakesController::class, 'downloadpartycakes'])->name('cake.download');

//API
Route::get('items/{storeids}', [ItemsAPIController::class, 'index']);
Route::get('foodpanda', [ItemsAPIController::class, 'foodpanda']);
Route::get('grabfood', [ItemsAPIController::class, 'grabfood']);
Route::get('manilaprice', [ItemsAPIController::class, 'manilaprice']);


Route::get('getallusers', [AuthController::class, 'getallusers']);
Route::get('bwcategory', [APIsController::class, 'bwcategory']);
Route::get('windowtables', [APIsController::class, 'windowtables']);
Route::get('windowtrans', [APIsController::class, 'windowtrans']);
Route::get('cashfunds', [APIsController::class, 'cashfunds']);
Route::get('discounts', [APIsController::class, 'discounts']);
Route::get('rbotransactionsalestrans', [APIsController::class, 'rbotransactionsalestrans']);
Route::get('rbotransactiontables', [APIsController::class, 'rbotransactiontables']);
Route::get('customers', [APIsController::class, 'customers']);

Route::get('/cash-funds-count', [CashFundController::class, 'getCashFundsCount']);
Route::post('/submit-cash-fund', [CashFundController::class, 'submitCashFund']);

Route::get('/cart/{windowId}', [POSController::class, 'cart']);
Route::post('/cart/clear', [POSController::class, 'clearCart']);
Route::post('/cart/update/{itemId}', [POSController::class, 'updateQuantity']);
Route::post('/cart/delete-multiple', [POSController::class, 'deleteMultiple']);
Route::post('/addtocart', [POSController::class, 'addToCart']);
Route::get('/cart2', [POSController::class, 'getCartItems']);
Route::get('ar', [APIsController::class, 'ar'])->name('ar');
Route::apiResource('discounts', DiscountController::class);
Route::post('update-cart-discount', [DiscountController::class, 'updateCartDiscount']);
Route::post('/submit-partial-payments', [POSController::class, 'submitPartialPayments']);
Route::post('/submit-remove-partial-payments', [POSController::class, 'submitRemovePartialPayments']);
Route::get('/mix-match/discounts', [MixAndMatchController::class, 'getDiscounts']);
Route::post('/received-update-all-counted-values', [ReceivedItemsController::class, 'updateAllCountedValues']);

/* Route::post('/StockCountingLine-update-all-counted-values', [StockCountingLineController::class, 'updateAllCountedValues']); */
/* Route::post('/stock-counting-line/update-all-counted-values ', [StockCountingLineController::class, 'updateAllCountedValues']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stock-counting-line/update-all-counted-values', [StockCountingLineController::class, 'updateAllCountedValues']);
}); */

Route::get('rbotransactionsalestrans', [APIsController::class, 'rbotransactionsalestrans']);
Route::get('rbotransactiontables', [APIsController::class, 'rbotransactiontables']);
Route::post('rbotransactiontables', [APIsController::class, 'storeRboTransactionTable']);
Route::post('rbotransactionsalestrans', [APIsController::class, 'storeRboTransactionSalesTrans']);

Route::post('rbotransactiontables/{store}/{zreadid}', [APIsController::class, 'zread']);

Route::get('getsummary/{storeid}', [APIsController::class, 'getsummary']);
Route::get('getdetails/{storeid}', [APIsController::class, 'getdetails']);
Route::get('getsequence/{storeid}', [APIsController::class, 'getsequence']);

Route::get('getStaffData/{storeid}', [APIsController::class, 'getStaffData']);  

Route::post('getdata/{storeid}/{getsummary}/{getdetails}', [APIsController::class, 'getdata']);
Route::post('getsequence/{storeid}/{nextrec}', [APIsController::class, 'postsequence']);


Route::get('getsequencevalues', [APIsController::class, 'nubersequencevalues']);

Route::get('transactionrefund/{storeid}', [APIsController::class, 'getTransactionRefund']);
Route::post('transactionrefund/{storeid}/{count}', [APIsController::class, 'transactionrefund']);

Route::get('/StockTransfer', [StockTransferController::class, 'create'])->name('StockTransfer');
Route::post('/StockTransfer', [StockTransferController::class, 'store']);
Route::post('/StockTransfer/update-all-counted-values ', [StockCountingLineController::class, 'updateAllCountedValues']); 

/* Route::get('/generate-xread', [POSController::class, 'generateXRead']);
Route::post('/print-xread', [POSController::class, 'printXRead']); */

Route::post('/stock-transfer-line/update-all-values', [StockTransferLineController::class, 'updateAllCountedValues']);


Route::middleware(['auth', 'role:STORE'])->group(function () {
    Route::post('/submit-order', [POSController::class, 'submitorder']);
});


Route::get('/loyalty-cards', [LoyaltyCardJsonController::class, 'index']);
Route::get('/loyalty-cards/{customerId}', [LoyaltyCardJsonController::class, 'show']);
Route::post('/loyalty-cards', [LoyaltyCardJsonController::class, 'store']);

Route::post('updatepoints/updatepoints/{card_number}/{points}', [LoyaltyCardJsonController::class, 'updatePointsByCardNumber'])
        ->name('loyalty-cards.updatePointsByCardNumber');

Route::put('/loyalty-cards/{loyaltyCard}', [LoyaltyCardJsonController::class, 'update']);
Route::post('/loyalty-cards/add-points', [LoyaltyCardJsonController::class, 'addPoints']);
Route::post('/loyalty-cards/redeem-points', [LoyaltyCardJsonController::class, 'redeemPoints']);
Route::delete('/loyalty-cards/{loyaltyCard}', [LoyaltyCardJsonController::class, 'destroy']);

Route::apiResource('stock-transfers', StockTransferController::class);
Route::post('stock-transfers/{transfer}/complete', [StockTransferController::class, 'complete']);

Route::apiResource('store-expenses', StoreExpenseController::class);
Route::post('store-expenses/{storeid}', [StoreExpenseController::class, 'updateExpense']);


Route::get('/attendance', [AttendanceRecordController::class, 'index']);
Route::get('/attendance/{id}', [AttendanceRecordController::class, 'show']);
Route::post('/attendance', [AttendanceRecordController::class, 'store']);
Route::put('/attendance/{id}', [AttendanceRecordController::class, 'update']);
Route::delete('/attendance/{id}', [AttendanceRecordController::class, 'destroy']);



/* Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('stock-counting')->group(function () {
        // Stock Counting Routes
        Route::get('/', [ApisStockCountingController::class, 'index']);
        Route::post('/', [ApisStockCountingController::class, 'store']);
        
        // Stock Counting Line Routes
        Route::get('line/{journalid}', [ApisStockCountingLineController::class, 'show']);
        Route::post('line', [ApisStockCountingLineController::class, 'store']);
        Route::post('line/generate-items', [ApisStockCountingLineController::class, 'generateItems']);
        Route::post('line/update-counts', [ApisStockCountingLineController::class, 'updateCounts']);
        Route::post('line/stock-transfer/{journalid}', [ApisStockCountingLineController::class, 'updateStockTransfer']);
        Route::post('line/post', [ApisStockCountingLineController::class, 'post']);
        Route::post('line/post-batch', [ApisStockCountingLineController::class, 'postBatch']);
    });
});
 */

  // Stock Counting Routes
  Route::get('stock-counting/{storeids}', [ApisStockCountingController::class, 'index']);
  Route::post('stock-counting/{storeids}/{posted}/{journalid}', [ApisStockCountingController::class, 'update']);
  
  // Stock Counting Line Routes
  Route::get('line/{storeName}/{journalId}', [ApisStockCountingLineController::class, 'show']);
  Route::post('line', [ApisStockCountingLineController::class, 'store']);
  Route::post('line/generate-items', [ApisStockCountingLineController::class, 'generateItems']);
  Route::post('line/update-counts', [ApisStockCountingLineController::class, 'updateCounts']);
  Route::post('line/stock-transfer/{journalid}', [ApisStockCountingLineController::class, 'updateStockTransfer']);
  Route::post('line/post', [ApisStockCountingLineController::class, 'post']);
  Route::post('line/post-batch', [ApisStockCountingLineController::class, 'postBatch']);
  
  Route::post('line/{itemid}/{storeid}/{journalid}/{adjustment}/{receivedcount}/{transfercount}/{wastecount}/{wastetype}/{counted}', [ApisStockCountingLineController::class, 'postbatchline']);
  
  Route::get('/api-attendance', [AttendanceApiController::class, 'index']);
  Route::get('/staff-date', [AttendanceApiController::class, 'getByStaffAndDate']);
  Route::get('/date-range', [AttendanceApiController::class, 'getByDateRange']);
  Route::get('/summary', [AttendanceApiController::class, 'getSummary']);
  Route::get('/staff/{staffId}', [AttendanceApiController::class, 'getByStaff']);
  Route::get('api-attendance/store/{storeId}', [AttendanceApiController::class, 'getByStore']);
  Route::get('/{id}', [AttendanceApiController::class, 'show']);
  
  
  
  
  
  