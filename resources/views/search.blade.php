@extends('welcome')

{{-- ---------------------------Cards Datos Productos------------------------------- --}}
@section('cero')
    @foreach ($val as $value)
        @foreach ($value as $item)
            <div class="col">
                <div class="card h-100 w-80 shadow">
                    @if (($item->precio!==$code)&&($item->precio>0))
                    <h5 class="card-header text-center bg-success font-weight-bold text-white">{{ $item->nombre_tienda }}</h5>
                    @else
                    <h5 class="card-header text-center">{{ $item->nombre_tienda }}</h5>
                    @endif
                    @if ($item->link=="#")
                    <img src={{ $item->imagen }} class="card-img-top mx-auto mt-2 w-50" alt="...">
                    @else
                    <a class="text-center" href={{ $item->link }} target="_blank">
                    <img src={{ $item->imagen }} class="card-img-top mx-auto mt-2 w-50" alt="...">
                    </a>
                    @endif

                    <div class="card-body">
                    @if (($item->precio!==$code)&&($item->precio>0))
                        <h6 class="card-title text-uppercase text-center">{{ $item->nombre_producto }}</h6>
                    @else
                        <h6 class="card-title text-uppercase text-center">{{ $item->nombre_producto }}</h6>
                    @endif
                    </div>

                    @if (($item->precio!==$code)&&($item->precio>0))
                    <div  class='card-footer text-center bg-success font-weight-bold text-white'>
                        <h5>{{ $item->precio }}</h5>
                    </div>
                    @else
                    <div class='card-footer card-text text-center'>
                        <h5 >{{ $item->precio }}</h5>
                    </div>
                    @endif
                </div>
            </div>


        @endforeach
    @endforeach
@endsection




</tbody>





