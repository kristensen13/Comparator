<?php

namespace App\Http\Controllers;

use Goutte\Client;
use Illuminate\Http\Request;

class SupersController extends Controller
{

    public function file_get_contents_curl($url)

    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_REFERER, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    protected function index(Request $request)

    {



        if (isset($_GET['codigo'])) :



            $code = $request->input('codigo');

            $this->validate($request, ['codigo' => 'numeric|required|integer|min:9999999|max:9999999999999']);



            //------------------------COALIMAR---------------------------------------

            $url0 = "https://www.coalimaronline.com/api/Articulo/PorCodigoBarras/$code.";

            $datos0 = $this->file_get_contents_curl($url0);
            //dd($datos0);

            $json0 = json_decode($datos0);

            if (!isset($json0) || $json0 == []) {

                $resJsonCoal =  [
                    "nombre_tienda" => "COALIMAR",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            } else {

                $valido = $json0[0]->EsVendible;

                if (($valido == "1") || (!is_null($json0[0]->Codigo))) {

                    if (isset($json0[0]->Id)) {

                        $id = $json0[0]->Id;

                        $foto = "https://www.coalimaronline.com/api/FotoDeProducto/PequePrincipalPorIdArticulo/$id";

                        $dataImg = $this->file_get_contents_curl($foto);
                        $jsonFoto = json_decode($dataImg);
                        //dd($jsonFoto);
                        if (isset($jsonFoto->NombreArchivo)) {

                            $nombreArchivo = $jsonFoto->NombreArchivo;
                        } else {

                            $imageCoal = asset('images/no-image.png');
                        }

                        $resJsonCoal = [
                            "nombre_tienda" => "COALIMAR",
                            "link" => "https://www.coalimaronline.com/producto?articulo=" . $json0[0]->Id,
                            "imagen" => "https://www.coalimaronline.com/assets/fotosArticulos/" . $nombreArchivo,
                            "nombre_producto" => $json0[0]->Nombre,
                            "precio" => number_format($json0[0]->Pvp, 2) . " €"
                        ];
                    }
                }
            }

            //----------------------EL CORTE INGLÉS------------------------------------

            $url1 = "https://www.elcorteingles.es/alimentacion/api/catalog/supermercado/type_ahead/?question=$code&scope=supermarket&center=010MOE&results=1";

            $datos1 = $this->file_get_contents_curl($url1);

            $json1 = json_decode($datos1);
            //dd($json1);

            if (isset($json1)) {

                $valido1 = $json1->catalog_result->products_list->total;

                $ean = $json1->term;

                if ($valido1 == "1") {

                    $resJsonCI = [
                        "nombre_tienda" => "EL CORTE INGLÉS",
                        "link" => "https://www.elcorteingles.es" . $json1->catalog_result->products_list->items[0]->product->pdp_url,
                        "imagen" => substr($json1->catalog_result->products_list->items[0]->product->media->thumbnail_url, 0, -9) . "600x600.jpg",
                        "nombre_producto" => $json1->catalog_result->products_list->items[0]->product->name[0],
                        "precio" => number_format(floatval($json1->catalog_result->products_list->items[0]->product->price->seo_price), 2) . " €"
                    ];
                } else {

                    $resJsonCI =  [
                        "nombre_tienda" => "EL CORTE INGLÉS",
                        "link" => "#",
                        "imagen" => "./images/no-existe.png",
                        "nombre_producto" => "No encontrado EAN:",
                        "precio" => "$code"
                    ];
                }
            } else {

                $resJsonCI =  [
                    "nombre_tienda" => "EL CORTE INGLÉS",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            //--------------------------CARREFOUR---------------------------

            $url2 = "https://www.carrefour.es/search-api/query/v1/search?query=$code&scope=desktop&lang=es&catalog=food&user=12e39324-7d92-445d-98cf-88b9a20042ac&session=7d99493f-f91b-496f-8482-7f956345ebf3&user_type=recurrent&rows=24&start=0&origin=history&f.op=OR";

            $datos2 = $this->file_get_contents_curl($url2);

            $json2 = json_decode($datos2);
            //dd($json2);

            if (isset($json2)) {

                $term = "virtualPage-Empathy";

                $valido2 = $json2->analytics->$term->searchNumResults;

                $ean2 = $json2->analytics->$term->searchParamsQuery;

                if (($valido2 == "0") || ($code !== $json2->content->docs[0]->ean13)) {

                    $resJsonCF = [
                        "nombre_tienda" => "CARREFOUR",
                        "link" => "#",
                        "imagen" => "./images/no-existe.png",
                        "nombre_producto" => "No encontrado EAN:",
                        "precio" => "$code"
                    ];
                } else {

                    $resJsonCF = [
                        "nombre_tienda" => "CARREFOUR",
                        "link" => "https://www.carrefour.es" . $json2->content->docs[0]->url,
                        "imagen" => substr($json2->content->docs[0]->image_path, 0, strpos($json2->content->docs[0]->image_path, "3")) . "600" . substr($json2->content->docs[0]->image_path, -31),
                        "nombre_producto" => $json2->content->docs[0]->display_name,
                        "precio" => number_format($json2->content->docs[0]->active_price, 2) . " €"
                    ];
                }
            } else {

                $resJsonCF = [
                    "nombre_tienda" => "CARREFOUR",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            //---------------------------DIA---------------------------------

            $url3 = "https://www.dia.es/compra-online/search/autocompleteSecure?term=$code&maxResults=10";

            $datos3 = $this->file_get_contents_curl($url3);

            $json3 = json_decode($datos3);
            //dd($json3);

            if (isset($json3)) {

                $valido3 = $json3->searchQuantity;

                if ($valido3 == "1") {

                    if (is_null($json3->lightProducts[0]->images)) {

                        $imageDIA = asset('./images/no-image.png');
                    } else {

                        $imageDIA = $json3->lightProducts[0]->images[1]->url;
                    }

                    $resJsonDIA = [
                        "nombre_tienda" => "DIA",
                        "link" => "https://www.dia.es/compra-online" . $json3->lightProducts[0]->url,
                        "imagen" => "$imageDIA",
                        "nombre_producto" => $json3->lightProducts[0]->name,
                        "precio" => number_format($json3->lightProducts[0]->price->value, 2) . " €"
                    ];
                } else {

                    $resJsonDIA = [
                        "nombre_tienda" => "DIA",
                        "link" => "#",
                        "imagen" => "./images/no-existe.png",
                        "nombre_producto" => "No encontrado EAN:",
                        "precio" => "$code"
                    ];
                }
            } else {

                $resJsonDIA = [
                    "nombre_tienda" => "DIA",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            //------------------------CONSUM--------------------------------

            $urlCSM = "https://www.bing.com/search?q=$code+consum";

            $client = new Client();

            $crawler = $client->request('GET', $urlCSM);

            $h1 = $crawler->filter('.b_algo h2 a')->text('href');

            $res = "";

            $crawler->filter('.b_algo h2 a')->each(function ($enlaces) use (&$res) {

                $h2 = [$enlaces->attr('href')];

                foreach ($h2 as $enlace) {

                    if ((strlen(stristr($enlace, 'https://tienda.consum.es/es/p/')) > 0) ||

                        (strlen(stristr($enlace, 'https://tienda.consum.es/vl/p/')) > 0)

                    ) {

                        $res = substr($enlace, -7);

                        $res = preg_replace('/[^0-9]/', '', $enlace);
                    }
                }
            });

            $url6 = "https://tienda.consum.es/api/rest/V1.0/catalog/product/code/$res";

            $curl = curl_init($url6);

            curl_setopt($curl, CURLOPT_URL, $url6);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);



            $headers = array(

                "Accept: application/json",

                "x-tol-zone: 495",

            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            //for debug only!

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $dat6 = curl_exec($curl);

            curl_close($curl);

            $json6 = json_decode($dat6);

            if (isset($json6->code)) {

                if (($json6->ean) == $code) {

                    $resJsonCSM = [
                        "nombre_tienda" => "CONSUM",
                        "link" => $json6->productData->url,
                        "imagen" => str_replace("135x135", "300x300", $json6->productData->imageURL), "nombre_producto" => $json6->productData->description,
                        "precio" => number_format($json6->priceData->prices[0]->value->centAmount, 2) . " €"
                    ];
                } else {

                    $resJsonCSM = [
                        "nombre_tienda" => "CONSUM",
                        "link" => "#",
                        "imagen" => "./images/no-existe.png",
                        "nombre_producto" => "No encontrado EAN:",
                        "precio" => "$code"
                    ];
                }
            } else {

                $resJsonCSM = [
                    "nombre_tienda" => "CONSUM",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            // ----------------------------PRIMOR----------------------------------------

            $url7 = "https://eu1-search.doofinder.com/5/search?hashid=a378e08709efceb049f93c1d6ca1ff60&query_counter=1&page=1&rpp=30&transformer=&query=$code";

            $curl = curl_init($url7);

            curl_setopt($curl, CURLOPT_URL, $url7);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(

                "origin: https://www.primor.eu",

            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            //for debug only!

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $datos7 = curl_exec($curl);

            curl_close($curl);

            $json7 = json_decode($datos7);

            if ($json7->total == 0) {

                $resJsonPRI = [
                    "nombre_tienda" => "PRIMOR",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            } elseif ($code == $json7->results[0]->ean13) {

                $resJsonPRI = [
                    "nombre_tienda" => "PRIMOR",
                    "link" => $json7->results[0]->link,
                    "imagen" => $json7->results[0]->image_link,
                    "nombre_producto" => $json7->results[0]->title,
                    "precio" => number_format($json7->results[0]->best_price, 2) . " €"
                ];
            } else {

                $resJsonPRI = [
                    "nombre_tienda" => "PRIMOR",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            //-------------------------PROVECAEX---------------------------------

            $url8 = "https://www.provecaex.com/module/stproductsearch/productsearch?cate=&q=$code&limit=10&timestamp=1627595851295&ajaxSearch=1&id_lang=1";

            $datos8 = $this->file_get_contents_curl($url8);

            $json8 = json_decode($datos8);
            //dd($json8);
            if (!isset($json8) || ($json8->products == [])) {
                $resJsonPRV = [
                    "nombre_tienda" => "PROVECAEX",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            } elseif ($json8->products[0]->availability == "available") {
                $resJsonPRV = [
                    "nombre_tienda" => "PROVECAEX",
                    "link" => $json8->products[0]->link,
                    "imagen" => $json8->products[0]->images[0]->medium->url,
                    "nombre_producto" => $json8->products[0]->name,
                    "precio" => number_format($json8->products[0]->price_amount, 2) . " €"
                ];
            } else {
                $resJsonPRV = [
                    "nombre_tienda" => "PROVECAEX",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => "$code"
                ];
            }

            //---------------------------ULABOX-----------------------------------------

            $url9 = "https://api.ulabox.com/api/v2/products/search?q=$code";

            $curl = curl_init($url9);

            curl_setopt($curl, CURLOPT_URL, $url9);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(

                "api-key: xhfh7KFJeQq8EENgVAM7",
                "origin: https://www.ulabox.com",
                "platform-id: flash"

            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            //for debug only!

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $datos9 = curl_exec($curl);

            curl_close($curl);

            $json9 = json_decode($datos9);

            if ($json9->meta->count > 0) {

                //Creando URL de la imagen
                $path = $json9->data[0]->attributes->medias->image[0]->path;
                $file = $json9->data[0]->attributes->medias->image[0]->file;
                $size = $json9->data[0]->attributes->medias->image[0]->sizes[2];
                $ext = $json9->data[0]->attributes->medias->image[0]->ext;

                $imageULA = $path . "/" . $file . "_" . $size . "." . $ext;

                $resJsonULA = [
                    "nombre_tienda" => "ULABOX",
                    "link" => "#", "imagen" => $imageULA,
                    "nombre_producto" => $json9->data[0]->attributes->name,
                    "precio" => number_format($json9->data[0]->attributes->price, 2) . " €"
                ];
            } else {

                $resJsonULA = [
                    "nombre_tienda" => "ULABOX",
                    "link" => "#",
                    "imagen" => "./images/no-existe.png",
                    "nombre_producto" => "No encontrado EAN:",
                    "precio" => $code
                ];
            }




            //-------------------------------------------------------------------------------------

            function array_sort_by(&$arrIni, $col, $order = SORT_NUMERIC)

            {

                $arrAux = array();

                foreach ($arrIni as $key => $row) {

                    //dd($row);

                    if ($row > 0) {

                        $arrAux[$key] = is_numeric($row) ? $arrAux[$key] = $row[$col] : $row[$col];

                        //$arrAux[$key] = strtolower($arrAux[$key]);

                    }
                }

                array_multisort($arrAux, $order, $arrIni);
            }

            $precio = ["Coalimar" => $resJsonCoal, "El Corte Inglés" => $resJsonCI, "Carrefour" => $resJsonCF, "DIA" => $resJsonDIA, "Consum" => $resJsonCSM, "Primor" => $resJsonPRI, "Provecaex" => $resJsonPRV, "Ulabox" => $resJsonULA];

            array_sort_by($precio, 'precio', $order = SORT_NUMERIC, SORT_ASC);

            $prec = ["tiendas" => $precio];


            $val = json_encode($prec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $val = json_decode($val);



            //-------------------------------------------------------------------------------------

            return view('search', compact('val', 'code'));

        endif;
    }
}
