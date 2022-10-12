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


{{-- ------------------Ingredientes y Alérgenos---------------------------------- --}}

@section('ingredientes')

<div class="card h-100 shadow">

  <h4 class="card-header">Ingredientes</h4>

  <div class="card-body">

    <p class="card-text text-capitalize">{{ $infoNutri->info_nutri->ingredientes }}</p>

  </div>

</div>

@endsection

@section('alergenos')

<div class="card h-100 shadow">

  <h4 class="card-header">Alérgenos</h4>

  <div class="card-body">

    <p class="card-text text-capitalize">{{ $infoNutri->info_nutri->alergenos }}</p>

  </div>

</div>

@endsection


{{-- ----------------Info Nutricional----------------------------- --}}

@section('nutri')

<thead style="background-color: #df612f">

  <tr>

    <th scope='col' class='text-white text-center'>Información Nutricional</th>

    <th scope='col' class='text-white text-center'>Por cada 100 g/100 ml</th>

  </tr>

</thead>

<tbody>

  <tr>

    <td>Energía(kcal)</td>

    <td class='text-center'>{{ $infoNutri->info_nutri->energia_kcal_100g}} {{ $infoNutri->info_nutri->energia_kcal_unit }}</td>

  </tr>

  <tr>

    <td>Energía</td>

    <td class='text-center' colspan='2'>{{ $infoNutri->info_nutri->energia_100g }} {{ $infoNutri->info_nutri->energia_unit }}</td>

  </tr>

  <tr>

    <td>Grasas<br> - Grasas Saturadas</td>

    <td class='text-center'>{{ $infoNutri->info_nutri->grasas_100g }} {{ $infoNutri->info_nutri->grasas_unit }}<br> {{ $infoNutri->info_nutri->grasas_saturadas_100g }} {{ $infoNutri->info_nutri->grasas_saturadas_unit }}</td>

  </tr>

  <tr>

    <td>Hidratos de carbono<br> - Azúcares</td>

    <td class='text-center'>{{ $infoNutri->info_nutri->hidratos_carbono_100g }} {{ $infoNutri->info_nutri->hidratos_carbono_unit }}<br> {{ $infoNutri->info_nutri->azucares_100g }} {{ $infoNutri->info_nutri->azucares_unit }}</td>

  </tr>

  <tr>

    <td>Proteínas</td>

    <td class='text-center' colspan='2'>{{ $infoNutri->info_nutri->proteinas_100g }} {{ $infoNutri->info_nutri->proteinas_unit }}</td>

  </tr>

  <tr>

    <td>Sal<br> - Sodio</td>

    <td class='text-center' colspan='2'>{{ $infoNutri->info_nutri->sal_100g }} {{ $infoNutri->info_nutri->sal_unit }}<br> {{ $infoNutri->info_nutri->sodio_100g }} {{ $infoNutri->info_nutri->sodio_unit }}</td>

  </tr>

</tbody>

@endsection



