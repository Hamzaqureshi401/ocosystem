<?php
/* Custom H2 Routes */
Route::get('show-hydrogen-view', 'H2Controller@showhydrogenView')->name('landing.ajax.hydrogen');
Route::any('/store/h2/receipt/{data?}', 'H2Controller@storeh2receipt')->name('store.h2.receipt');
?>
