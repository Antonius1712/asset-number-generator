<?php

namespace App\Console\Commands;

use App\Models\PO_Detail;
use App\Models\PO_Info;
use App\Models\PO_Log_Sending_Email;
use Illuminate\Console\Command;

class SendEmailEPO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:email-epo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending e-mail of EPO.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $PO_Log_Sending_Email = PO_Log_Sending_Email::where('email_sent', null)->get();
        $emailTemplate = 'email.sent-email-epo';

        foreach( $PO_Log_Sending_Email as $val ){
            $DETAIL = PO_Detail::where('PID_Header', $val->PID)->with('PO_Asset', 'branch', 'CT')->get();

            $PO_Info = PO_Info::where('PID_Header', $val->PID)->orderby('PID', 'desc')->first();

            if( isset($PO_Info->Email_Requester) && isset($PO_Info->Email_Checker) && isset($PO_Info->Email_Checker_Asset) ){
                $EmailTo = [$PO_Info->Email_Requester, $PO_Info->Email_Checker, $PO_Info->Email_Checker_Asset];
                $EmailCC = $PO_Info->Email_Approval;

                $PARAM = [
                    'PID' => $val->PID,
                    'DETAIL' => $DETAIL
                ];
                
                \Mail::send($emailTemplate, $PARAM,
                    function ($mail) use ($val, $EmailTo, $EmailCC) {
                        $mail->from(config('app.NO_REPLY_EMAIL'), config('app.name'));
                        $mail->to($EmailTo);
                        $mail->subject($val->email_subject);
                        $mail->cc($EmailCC);
                        $mail->bcc(['it-dba01@lippoinsurance.com', 'it-dba07@lippoinsurance.com']);
                    }
                ); 

                $val->email_sent = 'yes';
                $val->save();
            }
        }
    }
}
