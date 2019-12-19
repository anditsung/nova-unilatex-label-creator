<?php

Route::get('labels/{data}', \Anditsung\LabelCreator\Http\Controllers\PrintLabelController::class . '@labels');
