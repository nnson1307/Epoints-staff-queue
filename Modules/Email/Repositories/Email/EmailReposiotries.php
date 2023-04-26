<?php


namespace Modules\Email\Repositories\Email;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Email\Models\ConfigTable;
use Modules\Email\Models\EmailProviderTable;
use Modules\Email\Models\StaffEmailLogTable;

class EmailReposiotries implements EmailRepositoryInterface
{
    protected $email;
    protected $config;
    protected $emailProvider;

    public function __construct(StaffEmailLogTable $email , ConfigTable $config,EmailProviderTable $emailProvider){
        $this->email = $email;
        $this->config = $config;
        $this->emailProvider = $emailProvider;
    }

    public function sendEmail()
    {

        $get_provider = $this->emailProvider->getItem(1);
        if ($get_provider != null){
            //        Lấy danh sách email
            $listEmail = $this->email->getListEmailSend();
            foreach($listEmail as $itemLog){
                $this->email->editEmail($itemLog["staff_email_log_id"],[
                    "is_run"=>1,
                    "run_at"=>Carbon::now()
                ]);

                $to = $itemLog["email_to"];
//            $from = $itemLog["email_from"];
                $from = $get_provider->email;
                $email_subject = $itemLog["email_subject"];
                $params =(array) json_decode($itemLog["email_params"],true);
                $params['logo'] = $this->config->getByKey('logo')['value'];
                $type = $itemLog["email_type"];
//            $from_mail = env('MAIL_FROM_NAME');
                $from_mail = $get_provider->name_email;
                try{
                    if ($to != null && $to != '') {
//                    Gửi với clicksend
                        if ($get_provider->type == 'clicksend'){
                            //Lấy thông tin cấu hình email
                            $getInfoSendMail = $this->getEmailAddresses();
                            if ($getInfoSendMail['error'] == false){
                                $view = view('email::mail.'.$type,$params)->render();
                                $tmp = [
                                    'to' => [
                                        [
                                            'email' => $to,
                                            'name' => $to
                                        ]
                                    ],
                                    'from' => [
                                        'name' => $from,
                                        'email_address_id' => $getInfoSendMail['data']['email_address_id']
                                    ],
                                    'subject' => $email_subject,
                                    'body' => $view
                                ];
                                //Call send email click send
                                $test = $this->callApiClickSend('https://rest.clicksend.com/v3/email/send', 'post', $tmp);
                            }
                        } else {
                            Mail::send('email::mail.'.$type, $params, function($message) use($to,$from,$email_subject,$from_mail){
                                $message->from($from,$from_mail);
                                $message->to($to);
                                $message->subject($email_subject);
                            });
                        }

                        $this->email->editEmail($itemLog["staff_email_log_id"],[
                            "is_run"=>1,
                            "run_at"=>Carbon::now()
                        ]);

                    } else {
                        $this->email->editEmail($itemLog["staff_email_log_id"],[
                            "is_run"=>1,
                            "run_at"=>Carbon::now(),
                            "is_error"=>1,
                            "error_description"=> $to == null || $to == '' ? 'Không có email nhận' : 'Không có email gửi'
                        ]);
                    }
                }catch (Exception $exception){
                    $this->email->editEmail($itemLog["staff_email_log_id"],[
                        "is_run"=>1,
                        "run_at"=>Carbon::now(),
                        "is_error"=>1,
                        "error_description"=>$exception->getMessage()
                    ]);
                }
            }
        }
    }

    public function getEmailAddresses()
    {
        $email = $this->callApiClickSend('https://rest.clicksend.com/v3/email/addresses', 'get', []);
        if ($email['http_code'] == 200) {
            if (count($email['data']['data']) != 0) {
                foreach ($email['data']['data'] as $item) {
                    if ($item['verified'] == 1) {
                        return [
                            'error' => false,
                            'data' => $item
                        ];
                    }
                }
                return [
                    'error' => true,
                    'data' => null
                ];
            } else {
                return [
                    'error' => true,
                    'data' => null
                ];
            }
        } else {
            return [
                'error' => true,
                'data' => null
            ];
        }
    }


    public function callApiClickSend($_URL, $post, $sendEmail)
    {
        $provider = DB::table('email_provider')->where('id', 1)->first();
        $provider->password = Crypt::decryptString($provider->password);
        $oURL = curl_init();
        curl_setopt($oURL, CURLOPT_URL, $_URL);
//            curl_setopt($oURL, CURLOPT_HEADER, TRUE);
        if ($post == 'post') {
            curl_setopt($oURL, CURLOPT_POST, TRUE);
        } else {
            curl_setopt($oURL, CURLOPT_HTTPGET, TRUE);
        }
        curl_setopt($oURL, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($oURL, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($oURL, CURLOPT_USERPWD, $provider->email . ":" . $provider->password);
        if (count($sendEmail) != 0) {
            curl_setopt($oURL, CURLOPT_POSTFIELDS, json_encode($sendEmail));
        }
        $response = curl_exec($oURL);
//        $response = curl_getinfo($oURL, CURLINFO_HTTP_CODE);
        curl_close($oURL);
        return json_decode($response, true);
    }
}