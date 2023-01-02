<?php
/* Custom Pdf Download Routes */
  Route::get('download/pdf/{startDate?}/{endDate?}/{view?}/{system_id?}','PdfDownloadController@printPDF')->name('download.pdf');
Route::get('get/mer/id','PdfDownloadController@getmerid')->name('get.mer.id');


?>
