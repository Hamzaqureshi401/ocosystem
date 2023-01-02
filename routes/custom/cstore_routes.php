<?php
/* Custom C-Store Routes */
Route::get('show-cstore-view', 'CStoreController@showcstoreView')->name('landing.ajax.cstore');
Route::any('/store/cstore/receipt/{data?}', 'CStoreController@StoreCstoreReceipt')->name('store.cstore.receipt');
?>
