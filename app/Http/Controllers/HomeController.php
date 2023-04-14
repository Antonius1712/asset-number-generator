<?php

namespace App\Http\Controllers;

use App\Models\AssType;
use App\Models\PO_Asset;
use App\Models\PO_Detail;
use App\Models\PO_Header;
use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $params;
    
    
    public function index()
    {
        // dd('s', $this->RegisterAsset($this->params));
        // dd('s', $this->GetAssetDataSetFormat($this->params));

        return "<center> <b style='color: green; font-size: 48px;'> OK SUDAH MASUK <br/> TINGGAL JALANIN CRON. </b> </center>";

        return $this->GetAssetDataSetFormat($this->params);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function RegisterAsset($params)
    {
        $url = config('app.ws_care_url');
        $username = config('app.ws_care_username');
        $password = config('app.ws_care_password');

        $client = new \SoapClient($url, array(
            'trace' => 1,
            'encoding' => 'UTF-8',
            'soap_version' => SOAP_1_1,
            'use' => SOAP_LITERAL,
        ));

        $xml = array();
        $xml['dbUser'] = new \SoapVar($username, XSD_STRING, null, null, 'dbUser');
        $xml['dbPassword'] = new \SoapVar($password, XSD_STRING, null, null, 'dbPassword');
        foreach($params as $v)
        {
            $xml['InParam'][] = new \SoapVar($v, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema");
        }

        $data = $client->__SoapCall('RegisterAssetData', array($xml));

        return $data->OutParam->anyType;
    }

    public function GetAssetDataSetFormat($params){
        $url = config('app.ws_care_url_call_asset');
        $username = config('app.ws_care_username');
        $password = config('app.ws_care_password');

        $PO_Assets = PO_Asset::select('PID_Header')->distinct('PID_Header')->pluck('PID_Header')->toArray();
        // dd($PO_Assets);
        $PO_Headers = PO_Header::where('isAsset', '1')
            ->whereNotIn('PID', $PO_Assets)
            ->with('PO_Detail')
            ->withSum('PO_Detail', 'Qty')
        ->get();

        // dd($PO_Assets, $PO_Headers[0]->p_o__detail_sum_qty);

        // PO_DETAIL => HASMANY
        // 

        $register = [];

        foreach( $PO_Headers as $key => $PO_Header ){
            $PO_Detail = $PO_Header->PO_Detail;
            foreach( $PO_Detail as $keyD => $valD ){
                for( $q = 1; $q <= $valD->Qty; $q++  ){
                    if( $PO_Header->PaymentType == 1 ){
                        if( $PO_Header->isInstantPQ != 1 ){
                            // return false;
                            break;
                        }
                    }

                    // KALAU ADVANCE (1) HARUS ADA PQNO. ISINSTANTPQ ARUS 1.
                    // KALAU REIMBURSE (2) HARUS ADA PQNO.
                    if( $PO_Header->current_pqno ){
                        $ModelRequest = ModelsRequest::where('Request', $PO_Header->current_pqno)
                            ->with('pVoucher')
                        ->first();
                        
                        // DOCNO CHECK KE PVOUCHER AMBIL KOLOM DATE.
                        $body  = '<?xml version="1.0" encoding="utf-8"?>';
                        $body  .= '<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
                        $body  .= '<soap12:Body>';
                        $body  .= '<call_Asset xmlns="http://tempuri.org/">';
                        $body  .= '
                            <Asset>
                                <UserID>'.$username.'</UserID>
                                <Password>'.$password.'</Password>
                                <AssetID></AssetID>
                                <Description>'.$valD->Description.'</Description>
                                <AssetType>'.$valD->AssType.'</AssetType> 
                                <Date>'.date('Y-m-d', strtotime($ModelRequest->Date)).'</Date>
                                <Branch>'.$PO_Header->BranchId.'</Branch>
                                <CT>'.$valD->AssDepartment.'</CT>
                                <ID>'.$PO_Header->VendorId.'</ID>
                                <Currency>'.$PO_Header->Ccy.'</Currency>
                                <Rate>1</Rate>
                                <Qty>1</Qty>
                                <Price>'.$valD->UnitPrice.'</Price>
                                <DocNo>'.$ModelRequest->DocNo.'</DocNo>
                                <Location>'.$valD->AssLocation.'</Location>
                                <Attrib_1></Attrib_1>
                                <Attrib_2></Attrib_2>
                                <Attrib_3></Attrib_3>
                                <Attrib_4></Attrib_4>
                                <Attrib_5></Attrib_5>
                                <Attrib_6></Attrib_6>
                                <Attrib_7></Attrib_7>
                                <Attrib_8></Attrib_8>
                                <Attrib_9></Attrib_9>
                                <Attrib_10></Attrib_10>
                            </Asset>
                        ';
                        $body  .= '</call_Asset>';
                        $body  .= '</soap12:Body>';
                        $body  .= '</soap12:Envelope>';
    
                        $c = curl_init ($url);
                        curl_setopt ($c, CURLOPT_POST, true);
                        curl_setopt ($c, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                        curl_setopt ($c, CURLOPT_POSTFIELDS, $body);
                        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, false);
                        $response = curl_exec ($c);
                        curl_close ($c);
                        
                        $removeXml = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><call_AssetResponse xmlns="http://tempuri.org/"><call_AssetResult>';
                        $removeXml2 = '</call_AssetResult></call_AssetResponse></soap:Body></soap:Envelope>';
    
                        $response = str_replace($removeXml, '', $response);
                        $response = str_replace($removeXml2, '', $response);
    
                        // dd($response, $body);
    
                        $PO_Asset = new PO_Asset();
                        $PO_Asset->PID_Header = $PO_Header->PID;
                        $PO_Asset->AssetNo = $response;
                        $PO_Asset->date = date('Y-m-d', strtotime(now()));
                        $PO_Asset->time = date('H:i:s', strtotime(now()));
                        $PO_Asset->save();

                        // AssBranch.`
    
                        $VType = AssType::where('AssType', $valD->AssType)->value('VTRegister');
    
                        $params = [
                            'AssetID' => $response,
                            'VType' => $VType,
                            'CT' => $valD->AssDepartment,
                            'Remarks' => $valD->AssName.' '.$valD->AssBrand.' '.$valD->AssModel
                        ];
    
                        $register[] = $this->RegisterAsset($params);
                    }
                }
            }
        }

        return $register;
    }
}
