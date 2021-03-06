<?php

namespace Anditsung\LabelCreator\Http\Controllers;

use Anditsung\Manufacture\Models\Plant;
use Anditsung\Master\Models\Color;
use App\Http\Controllers\Controller;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Anditsung\LabelCreator\Models\LabelType as LabelTypeModel;

class PrintLabelController extends Controller
{
    public static function generatedNumber($number_string)
    {
        $numbers = array();
        $number_list = explode(",", $number_string);
        foreach($number_list as $number) {
            if(strpos($number, '~') !== false) {
                $number_range = explode('~', $number);
                $numbers = array_merge($numbers, range($number_range[0], $number_range[1]));
            }
            else {
                $number = array(intval($number));
                $numbers = array_merge($numbers, $number);
            }
        }

        return $numbers;
    }

    private function label_types()
    {
        return LabelTypeModel::all()->pluck('name', 'id');
    }

    private function plants()
    {
        return Plant::all()->pluck('name', 'id');
    }

    private function colors()
    {
        return Color::all()->pluck('name','id');
    }

    private function prepareFields($attributes)
    {
        $fields = [];
        foreach($attributes as $key => $value) {
            switch($key)
            {
//                case 'barcode':
//                    if($value) {
//                        $fields[] = Boolean::make('Barcode')->rules('required');
//                    }
//                    break;

                case 'plant':
                    if($value) {
                        $fields[] = Select::make('Plant')->options($this->plants())->rules('required');
                    }
                    break;

                case 'color':
                    if($value) {
                        $fields[] = Select::make('Color')->options($this->colors())->rules('required');
                    }
                    break;
            }
        }
        $fields[] = Text::make('Number')->rules('required')->help("Example:<br>1~10<br>1,3,5,11");
//        $fields[] = Number::make('Start')->rules('required');
//        $fields[] = Number::make('End')->rules('required');
        $fields[] = Number::make('Copy')->rules('required');

        return $fields;
    }

    private function dependFields()
    {
        $depentsField = [];

        foreach(LabelTypeModel::all() as $label_type) {

            $fields = $this->prepareFields($label_type->attributes);

            $depentsField[] = NovaDependencyContainer::make($fields)->dependsOn('label_type', $label_type->id);
        }

        return $depentsField;
    }

    public function fields(NovaRequest $request)
    {
        $fields = [
            Select::make('Label Type')
                ->options($this->label_types())
                ->rules('required'),
        ];

        $fields = array_merge($fields, $this->dependFields());

        return $fields;
    }

    public function printLabel(NovaRequest $request)
    {
        $data = $this->validateData($request);

        return $this->prepareData($request);
    }

    private function prepareData(NovaRequest $request)
    {
        $data = collect($request->all())->map(function($value, $key) {
            return $value;
        });

        return base64_encode(json_encode($data));
    }

    private function validateData(NovaRequest $request)
    {
        // cek number yang di input adalah angka
        $rules = collect($request->all())->map(function($value, $key) {
            return [$key => 'required'];
        })->toArray();

        Validator::make($request->all(), $rules)->validate();
    }

    //// WEB.PHP

    public function labels($data)
    {
        return view('label-creator::label', compact('data'));
    }
}
