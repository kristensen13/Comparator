<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DOMDocument;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Arr;

error_reporting(E_ALL ^ E_NOTICE);

class ProductController extends Controller
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

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function search(Request $request)

    {

        if (isset($_GET['codigo'])) :

            $this->validate($request, ['codigo' => 'required|integer|numeric|min:9999999|max:9999999999999']);

            $code = $request->input('codigo');



            //------------------------COALIMAR---------------------------------------

            
            $url0 = "https://www.coalimaronline.com/api/Articulo/PorCodigoBarras/$code.";

            $datos0 = $this->file_get_contents_curl($url0);
            //dd($datos0);

            $json0 = json_decode($datos0);

            if (!isset($json0) || $json0 == []) {

                $resJsonCoal =  ["nombre_tienda" => "COALIMAR", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                
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

                            $imageCoal = "https://www.coalimaronline.com/assets/fotosArticulos/" . $nombreArchivo;
                            
                        } else {

                            $imageCoal = asset('images/no-image.png');
                        }



                        $urlCoal = "https://www.coalimaronline.com/producto?articulo=" . $json0[0]->Id;

                        //dd($linkCoal);

                        $nameProdCoal = $json0[0]->Nombre;

                        $priceCoal = $json0[0]->Pvp;

                        //dd($priceCoal);

                        $priceCoal = number_format($priceCoal, 2) . " €";

                        $resJsonCoal = ["nombre_tienda" => "COALIMAR", "url" => $urlCoal, "imagen_url" => "$imageCoal", "nombre_producto" => "$nameProdCoal", "precio" => $priceCoal];
                    }
                }
            }



            //----------------------EL CORTE INGLÉS------------------------------------

           


            //--------------------------CARREFOUR---------------------------

            $url2 = "https://www.carrefour.es/search-api/query/v1/search?query=$code&scope=desktop&lang=es&catalog=food&user=12e39324-7d92-445d-98cf-88b9a20042ac&session=7d99493f-f91b-496f-8482-7f956345ebf3&user_type=recurrent&rows=24&start=0&origin=history&f.op=OR";

            $datos2 = $this->file_get_contents_curl($url2);

            $json2 = json_decode($datos2);

            if (!isset($json2)) {

                $resJsonCF =  ["nombre_tienda" => "CARREFOUR", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            } else {

                $term = "virtualPage-Empathy";

                $valido2 = $json2->analytics->$term->searchNumResults;

                $ean2 = $json2->analytics->$term->searchParamsQuery;

                if ($valido2 == "1") {

                    $urlCF = "https://www.carrefour.es" . $json2->content->docs[0]->url;

                    $img = $json2->content->docs[0]->image_path;

                    $resultado1 = substr($img, 0, strpos($img, "3"));

                    $resultado2 = substr($img, -31);

                    $imageCF = $resultado1 . "600" . $resultado2;

                    $nameProdCF = strtoupper($json2->content->docs[0]->display_name);

                    $priceCF = $json2->content->docs[0]->active_price;

                    $priceCF = number_format($priceCF, 2) . " €";

                    $resJsonCF = ["nombre_tienda" => "CARREFOUR", "url" => $urlCF, "imagen_url" => "$imageCF", "nombre_producto" => "$nameProdCF", "precio" => $priceCF];
                } else {

                    $resJsonCF =  ["nombre_tienda" => "CARREFOUR", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                }
            }



            //---------------------------DIA---------------------------------

            $url3 = "https://www.dia.es/compra-online/search/autocompleteSecure?term=$code&maxResults=10";

            $datos3 = $this->file_get_contents_curl($url3);

            $json3 = json_decode($datos3);

            if (!isset($json3)) {

                $resJsonDIA =  ["nombre_tienda" => "DIA", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            } else {

                $valido3 = $json3->searchQuantity;

                if ($valido3 == "1") {

                    $urlDIA = "https://www.dia.es/compra-online" . $json3->lightProducts[0]->url;

                    $imageDIA = $json3->lightProducts[0]->images[1]->url;

                    $nameProdDIA = strtoupper($json3->lightProducts[0]->name);

                    $priceDIA = $json3->lightProducts[0]->price->value;

                    $priceDIA = number_format($priceDIA, 2) . " €";

                    $resJsonDIA = ["nombre_tienda" => "DIA", "url" => $urlDIA, "imagen_url" => "$imageDIA", "nombre_producto" => "$nameProdDIA", "precio" => $priceDIA];
                } else {

                    $resJsonDIA = ["nombre_tienda" => "DIA", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                }
            }



            //---------------------------ALCAMPO-------------------------------

            $url5 = 'https://www.alcampo.es/compra-online/search/autocomplete/comp_00000669?term=' . $code;

            $datos5 = $this->file_get_contents_curl($url5);

            $json5 = json_decode($datos5);

            if (!isset($json5) || ($json5->products == [])) {

                $resJsonALC =  ["nombre_tienda" => "ALCAMPO", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            } else {



                //$valido5 = $json5->products[0]->stock->stockLevelStatus;

                $valido5 = $json5->products[0]->name;

                //if ($valido5 == "INSTOCK") {

                if (isset($valido5)) {

                    $urlALC = "https://www.alcampo.es/compra-online" . $json5->products[0]->url;

                    $imageALC = $json5->products[0]->images[1]->url;

                    $nameProdALC = strtoupper($json5->products[0]->description);

                    $priceALC = $json5->products[0]->price->value;

                    $priceALC = number_format($priceALC, 2) . " €";

                    $resJsonALC = ["nombre_tienda" => "ALCAMPO", "url" => $urlALC, "imagen_url" => "$imageALC", "nombre_producto" => "$nameProdALC", "precio" => $priceALC];
                } else {

                    $resJsonALC =  ["nombre_tienda" => "ALCAMPO", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                }
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

            if (!isset($json6->code)) {

                $resJsonCSM =  ["nombre_tienda" => "CONSUM", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            } else {

                if (($json6->ean) == $code) {

                    $urlCSM = $json6->productData->url;

                    $nameProdCSM = strtoupper($json6->productData->description);

                    $imgCSM = $json6->productData->imageURL;

                    $imageCSM = str_replace("135x135", "300x300", $imgCSM);

                    //print_r($imageCSM);

                    $priceCSM = $json6->priceData->prices[0]->value->centAmount;

                    $priceCSM = number_format($priceCSM, 2) . " €";

                    $resJsonCSM = ["nombre_tienda" => "CONSUM", "url" => $urlCSM, "imagen_url" => "$imageCSM", "nombre_producto" => "$nameProdCSM", "precio" => $priceCSM];
                } else {

                    $resJsonCSM =  ["nombre_tienda" => "CONSUM", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                }
            }

            //}



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

            if (($json7->total) == 1) {

                $urlPRI = $json7->results[0]->link;

                $nameProdPRI = strtoupper($json7->results[0]->title);

                $imagePRI = $json7->results[0]->image_link;

                $pricePRI = $json7->results[0]->best_price;

                $pricePRI = number_format($pricePRI, 2) . " €";

                $resJsonPRI = ["nombre_tienda" => "PRIMOR", "url" => $urlPRI, "imagen_url" => "$imagePRI", "nombre_producto" => "$nameProdPRI", "precio" => $pricePRI];
                
            } else {

                $resJsonPRI =  ["nombre_tienda" => "PRIMOR", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            }


            
            //-------------------------PROVECAEX---------------------------------

            $url8 = "https://www.provecaex.com/module/stproductsearch/productsearch?cate=&q=$code&limit=10&timestamp=1627595851295&ajaxSearch=1&id_lang=1";

            $datos8 = $this->file_get_contents_curl($url8);

            $json8 = json_decode($datos8);
            //dd($json8);
            if (!isset($json8) || ($json8->products == [])) {

                $resJsonPRV =  ["nombre_tienda" => "PROVECAEX", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
                
            } elseif ($json8->products[0]->availability == "available") {

                $urlPRV = $json8->products[0]->link;

                //dd($link);

                $nameProdPRV = $json8->products[0]->name;

                $imagePRV = $json8->products[0]->images[0]->bySize->stores_default->url;

                $pricePRV = $json8->products[0]->price_amount;

                //dd($pricePRV);

                $pricePRV = number_format($pricePRV, 2) . " €";

                $resJsonPRV = ["nombre_tienda" => "PROVECAEX", "url" => $urlPRV, "imagen_url" => "$imagePRV", "nombre_producto" => "$nameProdPRV", "precio" => $pricePRV];
                
            }else{
               
                $resJsonPRV =  ["nombre_tienda" => "PROVECAEX", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
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
            //dd($datos9);

            curl_close($curl);

            $json9 = json_decode($datos9);
            //dd($json9);

            if ($json9->data != []){
            //if ($json9->meta->count > 0){
                
                $urlULA = "#";
                
                $nameProdULA = $json9->data[0]->attributes->name;
                
                //Creando URL de la imagen
                $path = $json9->data[0]->attributes->medias->image[0]->path;
                $file = $json9->data[0]->attributes->medias->image[0]->file;
                $size = $json9->data[0]->attributes->medias->image[0]->sizes[2];
                $ext = $json9->data[0]->attributes->medias->image[0]->ext;
                
                $imageULA = $path . "/" . $file . "_" . $size . "." . $ext;
                
                $priceULA = $json9->data[0]->attributes->price;
                
                $priceULA = number_format($priceULA, 2) . " €";

                $resJsonULA = ["nombre_tienda" => "ULABOX", "url" => $urlULA, "imagen_url" => "$imageULA", "nombre_producto" => "$nameProdULA", "precio" => $priceULA];
            }else{
               
                $resJsonULA =  ["nombre_tienda" => "ULABOX", "url" => "", "imagen_url" => "", "nombre_producto" => "", "precio" => 0];
            }




            //------------------------INFORMACIÓN NUTRICIONAL-------------------------------

            $url4 = "https://world.openfoodfacts.org/api/v0/product/$code";

            $datos4 = $this->file_get_contents_curl($url4);

            $json4 = json_decode($datos4);

            if (isset($json4)) {

                $valido4 = $json4->status;

                if ($valido4 == "0") {

                    $energiaK = "";

                    $energiaKUnit = "";

                    $energia = "";

                    $energiaUnit = "";

                    $grasas = "";

                    $grasasUnit = "";

                    $grasasSat = "";

                    $grasasSatUnit = "";

                    $carHid = "";

                    $carHidUnit = "";

                    $azucares = "";

                    $azucaresUnit = "";

                    $proteinas = "";

                    $proteinasUnit = "";

                    $sal = "";

                    $salUnit = "";

                    $sodio = "";

                    $sodioUnit = "";

                    $codeProdNutri = $json4->code;

                    $ingredientes = "No Info";

                    $alergenos = "No Info";

                    $resJsonInfoNutri = ["info_nutricional" => $codeProdNutri, "resultado_busqueda" => $valido4];
                } else {

                    $eneKcal = "energy-kcal_100g";

                    $eneKcalUnit = "energy-kcal_unit";

                    $satFat = "saturated-fat_100g";

                    $satFatUnit = "saturated-fat_unit";

                    if (isset($json4->product->nutriments->$eneKcal)) {

                        $energiaK = $json4->product->nutriments->$eneKcal;

                        $energiaKUnit = $json4->product->nutriments->$eneKcalUnit;

                        if ($energiaK == "") {

                            $energiaK = "---";

                            $energiaKUnit = "---";
                        }
                    } else {

                        $energiaK = "---";

                        $energiaKUnit = "---";
                    }



                    if (isset($json4->product->nutriments->energy_100g)) {

                        $energia = $json4->product->nutriments->energy_100g;

                        $energiaUnit = $json4->product->nutriments->energy_unit;

                        if ($energia == "") {

                            $energia = "---";

                            $energiaUnit = "---";
                        }
                    } else {

                        $energia = "---";

                        $energiaUnit = "---";
                    }



                    if (isset($json4->product->nutriments->fat_100g)) {

                        $grasas = $json4->product->nutriments->fat_100g;

                        //dd($grasas);

                        $grasas = number_format($grasas, 2);

                        $grasasUnit = $json4->product->nutriments->fat_unit;

                        if ($grasas == "") {

                            $grasas = "---";

                            $grasasUnit = "---";
                        }
                    } else {

                        $grasas = "---";

                        $grasasUnit = "---";
                    }



                    if (isset($json4->product->nutriments->$satFat)) {

                        $grasasSat = $json4->product->nutriments->$satFat;

                        $grasasSat = number_format($grasasSat, 2);

                        $grasasSatUnit = $json4->product->nutriments->$satFatUnit;

                        if ($grasasSat == "") {

                            $grasasSat = "---";

                            $grasasSatUnit = "---";
                        }
                    } else {

                        $grasasSat = "---";

                        $grasasSatUnit = "---";
                    }



                    if (isset($json4->product->nutriments->carbohydrates_100g)) {

                        $carHid = $json4->product->nutriments->carbohydrates_100g;

                        $carHid = number_format($carHid, 2);

                        $carHidUnit = $json4->product->nutriments->carbohydrates_unit;

                        if ($carHid == "") {

                            $carHid = "---";

                            $carHidUnit = "---";
                        }
                    } else {

                        $carHid = "---";

                        $carHidUnit = "---";
                    }



                    if (isset($json4->product->nutriments->sugars_100g)) {

                        $azucares = $json4->product->nutriments->sugars_100g;

                        $azucares = number_format($azucares, 2);

                        $azucaresUnit = $json4->product->nutriments->sugars_unit;

                        if ($azucares == "") {

                            $azucares = "---";

                            $azucaresUnit = "---";
                        }
                    } else {

                        $azucares = "---";

                        $azucaresUnit = "---";
                    }



                    if (isset($json4->product->nutriments->proteins_100g)) {

                        $proteinas = $json4->product->nutriments->proteins_100g;

                        $proteinas = number_format($proteinas, 2);

                        $proteinasUnit = $json4->product->nutriments->proteins_unit;

                        if ($proteinas == "") {

                            $proteinas = "---";

                            $proteinasUnit = "---";
                        }
                    } else {

                        $proteinas = "---";

                        $proteinasUnit = "---";
                    }



                    if (isset($json4->product->nutriments->salt_100g)) {

                        $sal = $json4->product->nutriments->salt_100g;

                        $sal = number_format($sal, 2);

                        $salUnit = $json4->product->nutriments->salt_unit;

                        if ($sal == "") {

                            $sal = "---";

                            $salUnit = "---";
                        }
                    } else {

                        $sal = "---";

                        $salUnit = "---";
                    }



                    if (isset($json4->product->nutriments->sodium_100g)) {

                        $sodio = $json4->product->nutriments->sodium_100g;

                        $sodio = number_format($sodio, 2);

                        $sodioUnit = $json4->product->nutriments->sodium_unit;

                        if ($sodio == "") {

                            $sodio = "---";

                            $sodioUnit = "---";
                        }
                    } else {

                        $sodio = "---";

                        $sodioUnit = "---";
                    }



                    //--------------------------INGREDIENTES/ALÉRGENOS-----------------------------

                    if (isset($json4->product->product_name)) {

                        $nameProdNutri = strtoupper($json4->product->product_name);
                    } else {

                        $nameProdNutri = "";
                    }



                    $codeProdNutri = $json4->code;

                    if (isset($json4->product->ingredients_text_es)) {

                        $ingredientes = $json4->product->ingredients_text_es;

                        if ($ingredientes == "") {

                            $ingredientes = "No Info";
                        }
                    } else {

                        $ingredientes = "No Info";
                    }

                    if (isset($json4->product->allergens)) {

                        $alergenos = $json4->product->allergens;

                        if ($alergenos == "") {

                            $alergenos = "No Info";
                        }
                    } else {

                        $alergenos = "No Info";
                    }

                    $resJsonInfoNutri = ["nombre_producto" => "$nameProdNutri", "codigo_EAN" => $codeProdNutri, "ingredientes" => $ingredientes, "alergenos" => $alergenos, "energia_kcal_100g" => $energiaK, "energia_kcal_unit" => "$energiaKUnit", "energia_100g" => $energia, "energia_unit" => "$energiaUnit", "grasas_100g" => $grasas, "grasas_unit" => "$grasasUnit", "grasas_saturadas_100g" => $grasasSat, "grasas_saturadas_unit" => "$grasasSatUnit", "hidratos_carbono_100g" => $carHid, "hidratos_carbono_unit" => "$carHidUnit", "azucares_100g" => $azucares, "azucares_unit" => "$azucaresUnit", "proteinas_100g" => $proteinas, "proteinas_unit" => "$proteinasUnit", "sal_100g" => $sal, "sal_unit" => "$salUnit", "sodio_100g" => $sodio, "sodio_unit" => "$sodioUnit"];
                }
            } else {

                $eneriaK = "";

                $energiaKUnit = "";

                $energia = "";

                $energiaUnit = "";

                $grasas = "";

                $grasasUnit = "";

                $grasasSat = "";

                $grasasSatUnit = "";

                $carHid = "";

                $carHidUnit = "";

                $azucares = "";

                $azucaresUnit = "";

                $proteinas = "";

                $proteinasUnit = "";

                $sal = "";

                $salUnit = "";

                $sodio = "";

                $sodioUnit = "";

                $codeProdNutri = $json4->code;

                $resJsonInfoNutri = ["info_nutricional" => $codeProdNutri, "resultado_busqueda" => 0];
            }



            //-------------------------------------------------------------------------------------

            function array_sort_by(&$arrIni, $col, $order = SORT_ASC)

            {

                $arrAux = array();

                foreach ($arrIni as $key => $row) {

                    if ($row > 0) {

                        $arrAux[$key] = is_numeric($row) ? $arrAux[$key] = $row[$col] : $row[$col];

                        $arrAux[$key] = strtolower($arrAux[$key]);
                    }
                }

                array_multisort($arrAux, $order, $arrIni);
            }

            $precio = [$resJsonCoal, $resJsonCF, $resJsonDIA, $resJsonALC,  $resJsonCSM, $resJsonPRI, $resJsonPRV, $resJsonULA];



            array_sort_by($precio, 'precio', $order = SORT_ASC);

            $prec = ["tiendas" => $precio, "info_nutri" => $resJsonInfoNutri];



            //$val = json_encode($prec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);



            return response()->json($prec);





        endif;
    }
}
