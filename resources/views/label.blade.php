@extends('partials.layout')

@section('content')

    <?php

    $data = json_decode(base64_decode($data));

    $label_type = \Anditsung\LabelCreator\Models\LabelType::find($data->label_type);


    $columnTwo = true;
    $columnThree = "left";
    $labelCount = 1;

    $breakCount = $label_type->break_count;

    $barcode = "";
    $plant = "";
    $color = "";
    $number = "";

    if(isset($data->plant)) {
        $plant = \Anditsung\Manufacture\Models\Plant::find($data->plant)->name;
    }

    if(isset($data->color)) {
        $color = \Anditsung\Master\Models\Color::find($data->color)->code;
    }

    $numbers = \Anditsung\LabelCreator\Http\Controllers\PrintLabelController::generatedNumber($data->number);

    ?>
    @foreach($numbers as $number)
        @for($j = 1; $j <= $data->copy; $j++)
            <?php

                $design = $label_type->design;

                while(strlen($number) < $label_type->number_digits) {
                    $number = "0" . $number;
                }

                foreach($label_type->attributes as $name => $attribute) {
                    if($attribute) {
                        $key = "[" . strtoupper($name) . "]";
                        $replace = "";
                        switch($name)
                        {
                            case 'plant':
                                $replace = $plant;
                                break;

                            case 'color':
                                $replace = $color;
                                break;

                            case 'barcode':
                                $barcode = array(
                                    'plant' => $plant,
                                    'color' => $color,
                                    'number' => $number,
                                );
                                $replace = base64_encode(json_encode($barcode));
                                break;
                        }
                        $design = str_replace($key, $replace, $design);
                    }
                }

                $design = str_replace("[NUMBER]", $number, $design);

                $design_start = "<div class=\"";
                if($label_type->columns == 2) {
                    $design_start .= "flex w-1/2";
                }
                else if($label_type->columns == 3) {
                    $design_start .= "flex w-1/3";
                }
                $design = $design_start . " border-4 border-50 rounded-lg\" style=\"margin-left: 1.75rem; margin-right: 1.75rem; margin-top: 1.5rem\">" . $design . "</div>";
            ?>
            @if($label_type->columns == 1)
                {!! $design !!}
                @if($labelCount % $breakCount == 0)
                    <div style="page-break-after: always"></div>
                @endif
                <?php $labelCount++; ?>
            @elseif($label_type->columns == 2)
                @if($columnTwo)
                    <div class="flex">
                        {!! $design !!}
                    <?php $columnTwo = false; ?>
                @else
                        {!! $design !!}
                    </div>
                    <?php $columnTwo = true; ?>
                @endif
                @if($labelCount % $breakCount == 0)
                    <div style="page-break-after: always"></div>
                @endif
                <?php $labelCount++; ?>
            @elseif($label_type->columns == 3)
                @if($columnThree == 'left')
                    <div class="flex">
                        {!! $design !!}
                    <?php $columnThree = 'middle'; ?>
                @elseif($columnThree == 'middle')
                        {!! $design !!}
                    <?php $columnThree = 'right'; ?>
                @elseif($columnThree == 'right')
                        {!! $design !!}
                    </div>
                    <?php $columnThree = 'left'; ?>
                @endif
                @if($labelCount % $breakCount == 0)
                    <div style="page-break-after: always"></div>
                @endif
                <?php $labelCount++; ?>
            @endif
        @endfor
    @endforeach

    @if($label_type->columns == 2)
        @if(! $columnTwo)
            <div class="w-1/2" style="margin-left: 1.75rem; margin-right: 1.75rem; margin-top: 1.5rem;">
            </div>
        @endif
    @elseif($label_type->columns == 3)
        @if($columnThree == 'middle')
            <div class="w-1/3" style="margin-left: 1.75rem; margin-right: 1.75rem; margin-top: 1.5rem;">
            </div>
            <div class="w-1/3" style="margin-left: 1.75rem; margin-right: 1.75rem; margin-top: 1.5rem;">
            </div>
        @elseif($columnThree == 'right')
            <div class="w-1/3" style="margin-left: 1.75rem; margin-right: 1.75rem; margin-top: 1.5rem;">
            </div>
        @endif
    @endif

@endsection
