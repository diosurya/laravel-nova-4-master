<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quote_Company;

class CrazyDomainsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crazydomain:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $CrazyDomainsObj = \DB::connection('top4_main')
        ->table('Account')
        ->select(\DB::raw("Account.id,
                           Account.username,
                           Contact.first_name,
                           Contact.last_name,
                           Contact.company,
                           Contact.address,
                           Contact.address2,
                           Contact.phone,
                           Profile.friendly_url"))
        ->leftJoin('Contact', 'Account.id', '=', 'Contact.account_id')
        ->join('Profile', 'Account.id', '=', 'Profile.account_id')
        ->where('Account.importID', 10003)
        //->where('Account.entered', '>=', '2022-11-10 00:00:00')
        //->where('Account.entered', '<=', \Carbon\Carbon::now())
        ->whereRaw("Account.entered > NOW() - INTERVAL 12 MINUTE")
        ->get();

        echo "total listing scanned : ".collect($CrazyDomainsObj)->count()."\n";

       foreach($CrazyDomainsObj as $CrazyDomains){

        $first = isset($CrazyDomains->first_name) ? $CrazyDomains->first_name : '';
        $last = isset($CrazyDomains->last_name) ? $CrazyDomains->last_name : '';

        $client = \Bulldog\LaCrm\SimpleClient::create('254C5', '6W72XVBPSMMYX5JV4M3NV29J2B7GZ75BDWC67FPYZNH2316X03');
        $respon = $client->SearchContacts(new \Bulldog\LaCrm\Endpoints\Contacts\SearchContacts($CrazyDomains->username));
        $results = json_decode($respon->getBody()->getContents(), TRUE);
        $contact = collect($results['Result']);

        if($contact->count() < 1 ){
          try{
            $contactObj = new \Bulldog\LaCrm\Endpoints\Contacts\CreateContact;

            $contactObj->FirstName = $first;
            $contactObj->LastName = $last;
            $contactObj->Email = [[
                'Text'=> isset($CrazyDomains->username)? $CrazyDomains->username : '',
                'Type'=>'Work'
            ]];

            $contactObj->Phone = [[
                'Text'=> isset($CrazyDomains->phone)? $CrazyDomains->phone : '',
                'Type'=>'Work'
            ]];

            $result = $client->createContact($contactObj);
            $result = json_decode($result->getBody()->getContents(), TRUE);

            if($result['Success'] == true){
                echo "suksess create contact ".$CrazyDomains->username." \n";

                $groupObj = new \Bulldog\LaCrm\Endpoints\Contacts\AddContactToGroup(
                    $result['ContactId'],
                    'Top4_Sign_Up_Crazy_Domains');

                $grresult = $client->addContactToGroup($groupObj);
                $grresult = json_decode($grresult->getBody()->getContents(), TRUE);

                if($grresult['Success'] == true){
                    echo "suksess asign contact ".$CrazyDomains->username." to Top4_Sign_Up_Crazy_Domains group\n";
                }
            }

            }catch(\Exception $e){
               echo "failed create contact\n";
               \Log::info('failed create contact'.$e->getMessage());
            }
        }

              if(!Quote_Company::where('contact_email', $CrazyDomains->username)->exists()){
                 try{

                     $quote = new Quote_Company;
                     $quote->company_name = isset($CrazyDomains->company)? $CrazyDomains->company : '';
                     $quote->contact_name = $first." ".$last;
                     $quote->contact_email = isset($CrazyDomains->username)? $CrazyDomains->username : '';
                     $quote->contact_phone = isset($CrazyDomains->phone)? $CrazyDomains->phone : '';
                     $quote->contact_address1 = isset($CrazyDomains->address)? $CrazyDomains->address : '';
                     $quote->contact_address2 = isset($CrazyDomains->address2)? $CrazyDomains->address2 : '';
                     $quote->sales_id = 2;
                     $quote->comments = 'Top4 via Crazy Domains';
                     $quote->save();
                     echo $CrazyDomains->username." not available on quote imported .. \n";
                 }catch(\Exception $e){
                    echo "failled import ".$CrazyDomains->username." on quote \n";
                    \Log::info("failled import ".$CrazyDomains->username." on quote ".$e->getMessage());
                 }
              }


              $apiInstance = new \SendinBlue\Client\Api\ContactsApi(
                new \GuzzleHttp\Client(),
                \SendinBlue\Client\Configuration::getDefaultConfiguration()
                ->setApiKey(
                 'api-key',
                 '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'
               ));

               $sendinblue_contact_attributes = [
                'firstname'        => isset($CrazyDomains->first_name) ? $CrazyDomains->first_name : '',
                'lastname'         => isset($CrazyDomains->last_name) ? $CrazyDomains->last_name : '',
                'phone_number'     => isset($CrazyDomains->phone)? $CrazyDomains->phone : '',
                'TOP4_PROFILE_URL' => isset($CrazyDomains->friendly_url) ? 'https://www.top4.com.au/profile/'.$CrazyDomains->friendly_url : '',
                'Top4_Profile_ID'  => $CrazyDomains->id,
              ];

              try {
                   $createContact[0] = new \SendinBlue\Client\Model\CreateContact();
                   $createContact[0]['email'] = isset($CrazyDomains->username)? $CrazyDomains->username : '';
                   $createContact[0]['listIds'] = [311];
                   $createContact[0]['attributes'] = $sendinblue_contact_attributes;
                   $createContact[0]['updateEnabled'] = true;
                   $apiInstance->createContact($createContact[0]);
              }catch (\SendinBlue\Client\ApiException $e) {
                  Log::info('failed update sendinblue : '.$e);
              }


        }

        //dd(collect($results['Result'])->count());
    }
}
