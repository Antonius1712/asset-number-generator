<?php

namespace App\Console\Commands;

use App\Models\AssType;
use App\Models\PO_Asset;
use App\Models\PO_Asset_Log;
use App\Models\PO_Detail;
use App\Models\PO_Header;
use App\Models\PO_Log_Sending_Email;
use App\Models\Request;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAssetNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:asset-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generating Asset Number';

    private $url, $username, $password, $RED, $GREEN, $LC, $NC;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->url = config('app.ws_care_url_call_asset');
        $this->username = config('app.ws_care_username');
        $this->password = config('app.ws_care_password');

        $this->RED = "\033[1;31m";
        $this->GREEN = "\033[1;32m";
        $this->LC = "\033[1;36m"; # Light Cyan
        $this->NC = "\033[0m"; # No Color
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->GetAssetDataSetFormat();
    }

    public function RegisterAsset($params){
        ini_set('memory_limit', "4096M");
        ini_set('max_execution_time', 0);

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
        return $data;
    }

    public function GetAssetDataSetFormat(){
        $this->info($this->GREEN."Starting Process... \n");
        ini_set('memory_limit', "4096M");
        // ini_set('memory_limit', "123123M");
        ini_set('max_execution_time', 0);

        $url = $this->url;
        $username = $this->username;
        $password = $this->password;

        // $PO_Assets = PO_Asset::select('PID_Detail')
        //     ->pluck('PID_Detail')
        //     ->toArray();

        // dd($PO_Assets);

        $PO_Headers = PO_Header::where('isAsset', '1')
            ->where('IsCancelled', '!=', '1')
            // ->with(['PO_Detail'])
            ->whereHas('PO_Detail', function($query){
                $query->whereNotIn('PO_Detail.PID', function($q){
                    $q->select('PID_Detail')->from('PO_Asset');
                });
            })
            ->withSum('PO_Detail', 'Qty')
        ->get();

        $register = [];

        $count = $PO_Headers->pluck('p_o__detail_sum_qty')->sum();

        // dd($count);
        $this->output->progressStart($count);
        foreach( $PO_Headers as $key => $PO_Header ){
            //! KALAU PAYMENT TYPE ADVANCE (1) HARUS ADA PQNO. ISINSTANTPQ ARUS 1.
            // !KALAU PAYMENT TYPE REIMBURSE (2) HARUS ADA PQNO.
            if( $PO_Header->PaymentType == 1 ){
                if( $PO_Header->isInstantPQ != 1 ){
                    //? Tambahan 4 Jan 2024
                    if( $PO_Header->isSendCreatePQ == 1 ){
                        // $this->info("\n".$this->RED."isInstantPQ != 1");
                        $this->output->progressAdvance();
                        continue;
                    }
                }
            }

            if( $PO_Header->current_pqno ){
                $ModelRequest = Request::where('Request', $PO_Header->current_pqno)
                    ->with('pVoucher')
                ->first();
            }

            // dd($ModelRequest);

            if( isset($ModelRequest->Voucher) && $ModelRequest->Voucher != null ){
                if( $ModelRequest->pVoucher->Status != 'N' ){
                    $PO_Detail = $PO_Header->PO_Detail;
                    if( isset($ModelRequest) ){
                        foreach( $PO_Detail as $keyD => $valD ){
                            //! Validation.
                            $ValidateExistingAsset = PO_Asset::where('PID_Detail', $valD->PID)->first();
                            if( !$ValidateExistingAsset ){
                                for( $q = 1; $q <= $valD->Qty; $q++  ){
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
                                            <Date>'.date('Y-m-d', strtotime($ModelRequest->pVoucher->Date)).'</Date>
                                            <Branch>'.$valD->AssBranch.'</Branch>
                                            <CT>'.$valD->AssDepartment.'</CT>
                                            <ID>'.$PO_Header->VendorId.'</ID>
                                            <Currency>'.$PO_Header->Ccy.'</Currency>
                                            <Rate>1</Rate>
                                            <Qty>1</Qty>
                                            <Price>'.$valD->UnitPrice.'</Price>
                                            <DocNo>'.$ModelRequest->Voucher.'</DocNo>
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

                                    // dd($body);

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
                                    $PO_Asset->PID_Detail = $valD->PID;
                                    $PO_Asset->AssetNo = $response;
                                    $PO_Asset->date = date('Y-m-d', strtotime(now()));
                                    $PO_Asset->time = date('H:i:s', strtotime(now()));
                                    $PO_Asset->save();

                                    $VType = AssType::where('AssType', $valD->AssType)->value('VTRegister');

                                    $params = [
                                        'AssetID' => $response,
                                        'VType' => $VType,
                                        'CT' => $valD->AssDepartment,
                                        'Remarks' => $valD->AssName.' '.$valD->AssBrand.' '.$valD->AssModel
                                    ];

                                    $resultRegisterAsset = $this->RegisterAsset($params);
                                    if( isset($resultRegisterAsset->OutParam->anyType) ){
                                        $register[] = $resultRegisterAsset->OutParam->anyType;
                                    } else if( isset($resultRegisterAsset->ErrMsg) ){
                                        $PO_Asset_Log = new PO_Asset_Log();
                                        $PO_Asset_Log->PID_Detail = $valD->PID;
                                        $PO_Asset_Log->Message = $resultRegisterAsset->ErrMsg;
                                        $PO_Asset_Log->Date = date('Y-m-d', strtotime(now()));
                                        $PO_Asset_Log->Time = date('H:i:s', strtotime(now()));
                                        $PO_Asset_Log->save();
                                    }

                                    $this->output->progressAdvance();
                                }
                            }
                        }
                    }else{
                        $this->output->progressAdvance();
                        continue;
                    }

                    //! INSERT LOG EMAIL DISINI.
                    //! MIGRATION GANTI PO_Log_Sending_Email.
                    if( isset($ModelRequest) ){
                        $PID = $PO_Header->PID;
                        $DETAIL = PO_Detail::where('PID_Header', $PID)->with('PO_Asset', 'branch', 'CT')->get();
                        // dd($DETAIL[1]);

                        $PO_Log_Sending_Email = new PO_Log_Sending_Email();
                        $PO_Log_Sending_Email->PID = $PO_Header->PID;
                        $PO_Log_Sending_Email->email_subject = config('email.MAIL_SUBJECT_EPO').' '.$PO_Header->PID.' - '.$ModelRequest->Voucher;
                        $PO_Log_Sending_Email->email_body = view('email.sent-email-epo', compact('PID', 'DETAIL'))->render();
                        $PO_Log_Sending_Email->year = date('Y', strtotime(now()));
                        $PO_Log_Sending_Email->month = date('m', strtotime(now()));
                        $PO_Log_Sending_Email->date = date('D', strtotime(now()));
                        $PO_Log_Sending_Email->day = date('d', strtotime(now()));
                        $PO_Log_Sending_Email->time = date('H:i:s', strtotime( now() ));
                        $PO_Log_Sending_Email->save();
                    }
                }else{
                    $this->output->progressAdvance();
                    continue;
                }
            }else{
                $this->output->progressAdvance();
                continue;
            }
        }
        $this->output->progressFinish();

        return $register;
        $this->info($this->GREEN."Success Generating Asset Number.");
    }
}
